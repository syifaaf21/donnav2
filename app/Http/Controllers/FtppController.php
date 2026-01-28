<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\AuditeeAction;
use App\Models\AuditFindingSubKlausul;
use App\Models\CorrectiveAction;
use App\Models\Department;
use App\Models\DocumentFile;
use App\Models\FindingCategory;
use App\Models\HeadKlausul;
use App\Models\Klausul;
use App\Models\PreventiveAction;
use App\Models\Process;
use App\Models\Product;
use App\Models\Status;
use App\Models\SubAudit;
use App\Models\SubKlausul;
use App\Models\User;
use App\Models\WhyCauses;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;

class FtppController extends Controller
{
    public function index(Request $request)
    {
        // Build base query with eager loads
        $query = AuditFinding::with(['status', 'department', 'auditor', 'auditee', 'audit']);

        $user = auth()->user();

        // Determine roles lowercased for checks
        $userRolesLowercase = $user ? $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray() : [];

        // Get original roles (non-lowercased) for blade
        $userRoles = $user ? $user->roles->pluck('name')->toArray() : [];

        // Determine filter type: 'created' (default), 'assigned'
        $filterType = $request->input('filter_type', 'created');

        // Super Admin & Admin can see all records (no additional filter)
        if (!empty($user) && (in_array('super admin', $userRolesLowercase) || in_array('admin', $userRolesLowercase))) {
            // no department/audience filter
        }
        // Dept Head & Lead Auditor: can see all FTTP in their department(s), but exclude draft/draft finding
        elseif (!empty($user) && (in_array('dept head', $userRolesLowercase) || in_array('lead auditor', $userRolesLowercase))) {
            $userDeptIds = $user->departments->pluck('id')->toArray();
            if (empty($userDeptIds) && !empty($user->department_id)) {
                $userDeptIds = [(int) $user->department_id];
            }

            if (!empty($userDeptIds)) {
                $query->whereIn('department_id', $userDeptIds)
                    ->whereHas('status', function ($qs) {
                        $qs->whereRaw('LOWER(name) NOT IN (?, ?)', ['draft', 'draft finding']);
                    });
            } else {
                // if dept head/lead auditor has no department assigned, show nothing
                $query->whereRaw('0 = 1');
            }
        }
        // Auditor: apply filter based on filter_type
        elseif (!empty($user) && in_array('auditor', $userRolesLowercase)) {
            // Auditor tidak boleh melihat status draft, tapi boleh melihat draft finding
            if ($filterType === 'assigned') {
                // Show FTPP where user is in auditee (assigned to them)
                $query->whereHas('auditee', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                // auditee view should not see draft findings
                ->whereHas('status', function ($qs) {
                    $qs->whereRaw('LOWER(name) <> ?', ['draft finding']);
                });
            } else {
                // Show FTPP where user is auditor (created by them)
                $query->where('auditor_id', $user->id);
            }
        }
        // Default: only show FTTP where user is auditor OR listed as auditee
        else {
            if (empty($user)) {
                // not authenticated -> no records
                $query->whereRaw('0 = 1');
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('auditor_id', $user->id)
                        ->orWhereHas('auditee', function ($qa) use ($user) {
                            $qa->where('users.id', $user->id);
                        });
                })
                // for non-auditor users (auditee) do not show draft findings
                ->whereHas('status', function ($qs) {
                    $qs->whereRaw('LOWER(name) <> ?', ['draft finding']);
                });
            }
        }

        if ($request->filled('status_id')) {
            $statusIds = (array) $request->input('status_id');
            if (!empty($statusIds)) {
                $query->whereIn('status_id', $statusIds);
            }
        }

        // Optional free-text search across several columns/relations (kept safe)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhereHas('auditee', function ($qa) use ($search) {
                        $qa->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('auditor', function ($qat) use ($search) {
                        $qat->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('department', function ($qd) use ($search) {
                        $qd->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('status', function ($qs) use ($search) {
                        $qs->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply audit type filter if provided
        if ($request->filled('audit_type')) {
            $query->where('audit_type_id', $request->input('audit_type'));
        }

        // order and paginate
        $findings = $query->orderBy('updated_at', 'desc')->paginate(10);
        // preserve filters in pagination links
        $findings->appends($request->except('page'));

        // Lists for filters and sidebar (include counts)
        // Count berdasarkan department user jika bukan admin/super admin
        if (!empty($user) && (in_array('super admin', $userRolesLowercase) || in_array('admin', $userRolesLowercase))) {
            // Admin/Super Admin: hitung semua dokumen
            $statuses = Status::withCount('auditFinding')->orderBy('name')->get();
            $totalCount = AuditFinding::count();
        } elseif (!empty($user) && (in_array('dept head', $userRolesLowercase) || in_array('lead auditor', $userRolesLowercase))) {
            // Dept Head & Lead Auditor: hitung hanya dokumen dari department mereka
            $userDeptIds = $user->departments->pluck('id')->toArray();
            if (empty($userDeptIds) && !empty($user->department_id)) {
                $userDeptIds = [(int) $user->department_id];
            }

            $statuses = Status::withCount(['auditFinding' => function ($q) use ($userDeptIds) {
                if (!empty($userDeptIds)) {
                    $q->whereIn('department_id', $userDeptIds);
                }
            }])->orderBy('name')->get();

            $totalCount = !empty($userDeptIds) ? AuditFinding::whereIn('department_id', $userDeptIds)->count() : 0;
        } elseif (!empty($user) && in_array('auditor', $userRolesLowercase)) {
            // Auditor: hitung berdasarkan filter_type
            if ($filterType === 'assigned') {
                $statuses = Status::withCount(['auditFinding' => function ($q) use ($user) {
                    $q->whereHas('auditee', function ($qa) use ($user) {
                        $qa->where('users.id', $user->id);
                    });
                }])->orderBy('name')->get();

                $totalCount = AuditFinding::whereHas('auditee', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })->count();
            } else {
                $statuses = Status::withCount(['auditFinding' => function ($q) use ($user) {
                    $q->where('auditor_id', $user->id);
                }])->orderBy('name')->get();

                $totalCount = AuditFinding::where('auditor_id', $user->id)->count();
            }
        } else {
            // User lain: hitung dokumen yang terkait dengan mereka
            $statuses = Status::withCount(['auditFinding' => function ($q) use ($user) {
                if (!empty($user)) {
                    $q->where(function ($qa) use ($user) {
                        $qa->where('auditor_id', $user->id)
                            ->orWhereHas('auditee', function ($qb) use ($user) {
                                $qb->where('users.id', $user->id);
                            });
                    });
                }
            }])->orderBy('name')->get();

            $totalCount = !empty($user) ? AuditFinding::where(function ($q) use ($user) {
                $q->where('auditor_id', $user->id)
                    ->orWhereHas('auditee', function ($qa) use ($user) {
                        $qa->where('users.id', $user->id);
                    });
            })->count() : 0;
        }

        $departments = Department::orderBy('name')->get();

        // audit types for filter tabs
        $auditTypes = \App\Models\Audit::orderBy('name')->get();

        // auditors: users with role 'auditor' (case-insensitive)
        $auditors = User::whereHas('roles', function ($q) {
            $q->whereRaw('LOWER(name) = ?', ['auditor']);
        })->orderBy('name')->get();

        $nearDueFindings = AuditFinding::whereNotNull('due_date')
            ->whereDate('due_date', '>=', now()->today())
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->with(['auditor', 'auditee', 'department'])
            ->get();

        $overdueFindings = AuditFinding::whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->today())
            ->with(['auditor', 'auditee', 'department'])
            ->get();

        // admin users (case-insensitive)
        $adminUsers = User::whereHas('roles', fn($q) => $q->whereRaw('LOWER(name) = ?', ['admin']))->get();

        // Removed invalid access to $findings->auditor / ->auditee (paginator doesn't expose relations directly)
        // If you need to compute a notifiable user list for a specific finding, build it per-finding where needed.

        return view('contents.ftpp2.index', compact(
            'findings',
            'statuses',
            'departments',
            'auditors',
            'totalCount',
            'nearDueFindings',
            'overdueFindings',
            'filterType',
            'userRoles',
            'auditTypes'
        ));
    }

    public function getData($auditTypeId)
    {
        $auditType = Audit::with('subAudit')->findOrFail($auditTypeId);

        // Use the shared generator so numbering always takes the highest existing record
        $code = $this->generateRegistrationNumber($auditTypeId);

        // Get auditors that have this audit type in their pivot table
        $auditors = User::whereHas('roles', fn($q) => $q->where('tm_roles.id', 4)) // Role auditor
            ->whereHas('auditTypes', fn($q) => $q->where('tm_audit_types.id', $auditTypeId))
            ->get();

        return response()->json([
            'reg_number' => $code,
            'sub_audit'  => $auditType->subAudit,
            'auditors'   => $auditors,
        ]);
    }

    public function generateRegistrationNumber($auditTypeId)
    {
        // Get the audit type
        $auditType = Audit::findOrFail($auditTypeId);

        // If no custom format, fallback to old logic
        if (empty($auditType->registration_number_format)) {
            $year = now()->year;
            $prefix = $auditType->prefix_code ?? (($auditTypeId == 1) ? 'MS' : 'MR');

            $lastRecord = AuditFinding::where('registration_number', 'like', "{$prefix}/FTPP/{$year}/%")
                ->orderByRaw("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(registration_number, '/', 4), '/', -1) AS UNSIGNED) DESC")
                ->first();

            $nextNumber = 1;
            if ($lastRecord) {
                preg_match("/{$prefix}\/FTPP\/{$year}\/(\d+)\/\d+/", $lastRecord->registration_number, $matches);
                if (!empty($matches[1])) {
                    $nextNumber = (int) $matches[1] + 1;
                }
            }

            $findingNumber  = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $revisionNumber = str_pad(0, 2, '0', STR_PAD_LEFT);
            return "{$prefix}/FTPP/{$year}/{$findingNumber}/{$revisionNumber}";
        }

        // Parse custom format
        $format = $auditType->registration_number_format;
        $result = '';
        $counterPattern = ''; // pattern untuk cari last record
        $counterDigits = 0;
        $hasCounter = false;

        // Parse format character by character
        $i = 0;
        $length = strlen($format);
        while ($i < $length) {
            $char = $format[$i];

            // Check if it's a separator
            if (in_array($char, ['-', '/', '.'])) {
                $result .= $char;
                $counterPattern .= preg_quote($char, '/');
                $i++;
                continue;
            }

            // Check for component patterns
            if (substr($format, $i, 6) === 'PREFIX') {
                $prefix = $auditType->prefix_code ?? 'FTPP';
                $result .= $prefix;
                $counterPattern .= preg_quote($prefix, '/');
                $i += 6;
            } elseif (substr($format, $i, 4) === 'YYYY') {
                $year = now()->year;
                $result .= $year;
                $counterPattern .= $year;
                $i += 4;
            } elseif (substr($format, $i, 2) === 'YY') {
                $year = now()->format('y');
                $result .= $year;
                $counterPattern .= $year;
                $i += 2;
            } elseif (substr($format, $i, 2) === 'MM') {
                $month = now()->format('m');
                $result .= $month;
                $counterPattern .= $month;
                $i += 2;
            } elseif (substr($format, $i, 4) === 'NNNN') {
                $hasCounter = true;
                $counterDigits = 4;
                $result .= '{COUNTER}';
                $counterPattern .= '(\d+)';
                $i += 4;
            } elseif (substr($format, $i, 3) === 'NNN') {
                $hasCounter = true;
                $counterDigits = 3;
                $result .= '{COUNTER}';
                $counterPattern .= '(\d+)';
                $i += 3;
            } elseif (substr($format, $i, 3) === 'REV') {
                $result .= '00'; // default revision
                $counterPattern .= '\d+';
                $i += 3;
            } else {
                // Unknown character, just append
                $result .= $char;
                $counterPattern .= preg_quote($char, '/');
                $i++;
            }
        }

        // If format has counter (NNN or NNNN), find the highest existing number
        $nextNumber = 1;
        if ($hasCounter) {
            // Build pattern for searching - replace {COUNTER} with wildcard for LIKE
            $searchPattern = str_replace('{COUNTER}', str_repeat('_', $counterDigits), $result);

            // Find last record matching the pattern (without counter)
            $lastRecord = AuditFinding::where('registration_number', 'like', str_replace('{COUNTER}', '%', $result))
                ->orderBy('id', 'desc')
                ->first();

            if ($lastRecord) {
                // Extract counter using regex
                if (preg_match('/' . $counterPattern . '/', $lastRecord->registration_number, $matches)) {
                    if (!empty($matches[1])) {
                        $nextNumber = (int) $matches[1] + 1;
                    }
                }
            }

            // Replace {COUNTER} with padded number
            $result = str_replace('{COUNTER}', str_pad($nextNumber, $counterDigits, '0', STR_PAD_LEFT), $result);
        }

        return $result;
    }

    public function filterKlausul($auditType)
    {
        // Get klausuls based on audit_type_id
        $klausuls = Klausul::where('audit_type_id', $auditType)->get();

        return response()->json($klausuls);
    }

    public function getHeadKlausul($klausulId)
    {
        $headKlausuls = HeadKlausul::where('klausul_id', $klausulId)->get();
        return response()->json($headKlausuls);
    }

    public function getSubKlausul($headId)
    {
        $subKlausuls = SubKlausul::where('head_klausul_id', $headId)->get();
        return response()->json($subKlausuls);
    }

    public function getDepartments($plant)
    {
        $departments = Department::when($plant, function ($q) use ($plant) {
            $q->where('plant', $plant);
        }, function ($q) {
            // jika plant == 'All', tampilkan semua department (atau sesuai kebijakan)
            $q;
        })
            ->get(['id', 'name']);

        return response()->json($departments);
    }

    // sebelumnya named getProcess -> sekarang getProcesses
    public function getProcesses($plant)
    {
        try {
            $processes = Process::when($plant, function ($q) use ($plant) {
                $q->where('plant', $plant);
            }, function ($q) {
                $q;
            })
                ->get(['id', 'name']);

            return response()->json($processes);
        } catch (\Exception $e) {
            \Log::error('Error getProcesses: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // sebelumnya getProduct -> sekarang getProducts
    public function getProducts($plant)
    {
        $products = Product::when($plant, function ($q) use ($plant) {
            $q->where('plant', $plant);
        }, function ($q) {
            $q;
        })
            ->get(['id', 'name']);

        return response()->json($products);
    }

    public function getAuditee($departmentId)
    {
        // Ambil auditee berdasarkan department
        $auditees = User::whereHas('departments', fn($q) => $q->where('tm_departments.id', $departmentId))->get(['id', 'name']);

        return response()->json($auditees);
    }

    public function show($id)
    {
        $finding = AuditFinding::with([
            'auditeeAction',
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file'
        ])->findOrFail($id);

        // Merge all evidence from auditeeAction->file (auditee_action_id)
        $evidenceFiles = collect();
        if ($finding->auditeeAction && $finding->auditeeAction->file) {
            $evidenceFiles = $evidenceFiles->merge($finding->auditeeAction->file);
        }

        return view('contents.ftpp2.partials.detail', compact('finding', 'evidenceFiles'));
    }

    public function previewPdf($id)
    {
        // Ambil data finding beserta relasi
        $finding = AuditFinding::with([
            'audit',
            'subAudit',
            'findingCategory',
            'auditor',
            'auditee',
            'department',
            'process',
            'product',
            'subKlausuls',
            'file',
            'status',
            'auditeeAction',
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file', // lampiran
        ])->findOrFail($id);

        // Set stamp image URLs when signature/approval flags are set (value == 1)
        if ($finding->auditeeAction) {
            // Dept head signature (show manager approval image)
            if (!empty($finding->auditeeAction->dept_head_signature) && $finding->auditeeAction->dept_head_signature == 1) {
                $finding->dept_head_signature_url = public_path('images/mgr-approve.png');
            }

            // Leader / Supervisor signature (show user approval image)
            if (!empty($finding->auditeeAction->ldr_spv_signature) && $finding->auditeeAction->ldr_spv_signature == 1) {
                $finding->ldr_spv_signature_url = public_path('images/usr-approve.png');
            }

            // Acknowledge by lead auditor: if flag present on finding or auditeeAction, show lead auditor stamp
            if (
                (!empty($finding->acknowledge_by_lead_auditor) && $finding->acknowledge_by_lead_auditor == 1)
                || (!empty($finding->auditeeAction->acknowledge_by_lead_auditor) && $finding->auditeeAction->acknowledge_by_lead_auditor == 1)
            ) {
                $finding->acknowledge_by_lead_auditor_url = public_path('images/stamp-lead-auditor.png');
            }

            // Verified by auditor: if flag present on finding or auditeeAction, show internal auditor stamp
            if (
                (!empty($finding->verified_by_auditor) && $finding->verified_by_auditor == 1)
                || (!empty($finding->auditeeAction->verified_by_auditor) && $finding->auditeeAction->verified_by_auditor == 1)
            ) {
                $finding->verified_by_auditor_url = public_path('images/stamp-internal-auditor.png');
            }
        } else {
            // In case auditeeAction is absent but flags are on the finding
            if (!empty($finding->dept_head_signature) && $finding->dept_head_signature == 1) {
                $finding->dept_head_signature_url = public_path('images/mgr-approve.png');
            }
            if (!empty($finding->ldr_spv_signature) && $finding->ldr_spv_signature == 1) {
                $finding->ldr_spv_signature_url = public_path('images/usr-approve.png');
            }
            if (!empty($finding->acknowledge_by_lead_auditor) && $finding->acknowledge_by_lead_auditor == 1) {
                $finding->acknowledge_by_lead_auditor_url = public_path('images/stamp-lead-auditor.png');
            }
            if (!empty($finding->verified_by_auditor) && $finding->verified_by_auditor == 1) {
                $finding->verified_by_auditor_url = public_path('images/stamp-internal-auditor.png');
            }
        }

        // --- Ensure all attachment items have a full filesystem URL so DomPDF can embed images ---
        if ($finding->file) {
            foreach ($finding->file as $file) {
                $publicPath = public_path('storage/' . $file->file_path);
                $diskPath = storage_path('app/public/' . $file->file_path);

                // Prefer public/storage (symlink) because DomPDF chroot includes public path by default.
                if (file_exists($publicPath)) {
                    $file->full_url = $publicPath;
                } elseif (file_exists($diskPath)) {
                    // fallback to absolute file URI (may require chroot override)
                    $file->full_url = 'file://' . $diskPath;
                } else {
                    $file->full_url = null;
                }
            }
        }

        // Tambah filesystem path untuk semua lampiran pada auditeeAction (lampiran auditee) so DomPDF can embed them
        if ($finding->auditeeAction && $finding->auditeeAction->file) {
            foreach ($finding->auditeeAction->file as $file) {
                $publicPath = public_path('storage/' . $file->file_path);
                $diskPath = storage_path('app/public/' . $file->file_path);

                if (file_exists($publicPath)) {
                    $file->full_url = $publicPath;
                } elseif (file_exists($diskPath)) {
                    $file->full_url = 'file://' . $diskPath;
                } else {
                    $file->full_url = null;
                }
            }
        }
        // --- end attach full_url block ---

        // Generate main PDF
        // ensure DomPDF can access local files: enable remote and set chroot to project base
        $mainPdf = PDF::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => base_path(), // allow access to public/storage and storage paths
        ])->loadView('contents.ftpp2.pdf', compact('finding'))->output();

        if (!class_exists('\\setasign\\Fpdi\\Fpdi')) {
            // FPDI not installed — return main PDF directly for preview
            $tmpMain = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ftpp_main_' . uniqid() . '.pdf';
            file_put_contents($tmpMain, $mainPdf);
            return response()->file($tmpMain);
        }

        $merger = new \setasign\Fpdi\Fpdi();

        // save main PDF to a unique temp file
        $tmpMain = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ftpp_main_' . uniqid() . '.pdf';
        file_put_contents($tmpMain, $mainPdf);

        // Only merge once: main PDF, then covers+attachments
        $pageCount = $merger->setSourceFile($tmpMain);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $merger->importPage($i);
            $size = $merger->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $merger->AddPage($orientation, [$size['width'], $size['height']]);
            $merger->useTemplate($tplId);
        }

        // Helper to generate a cover PDF and return its temp path
        $generateCoverPdf = function($coverTitle) {
            $coverHtml = view('contents.ftpp2.pdf_cover', ['coverTitle' => $coverTitle])->render();
            $coverPdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => base_path(),
            ])->loadHTML($coverHtml)->output();
            $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ftpp_cover_' . uniqid() . '.pdf';
            file_put_contents($tmp, $coverPdf);
            return $tmp;
        };

        // Separate auditor and auditee PDF files
        $auditorPdfFiles = [];
        if (!empty($finding->file)) {
            foreach ($finding->file as $file) {
                $path = storage_path('app/public/' . $file->file_path);
                if (file_exists($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
                    $auditorPdfFiles[] = $path;
                }
            }
        }
        $auditeePdfFiles = [];
        if (!empty($finding->auditeeAction) && !empty($finding->auditeeAction->file)) {
            foreach ($finding->auditeeAction->file as $file) {
                $path = storage_path('app/public/' . $file->file_path);
                if (file_exists($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
                    $auditeePdfFiles[] = $path;
                }
            }
        }

        // Insert cover and files for auditor, then auditee
        if (count($auditorPdfFiles)) {
            $coverAuditor = $generateCoverPdf('ATTACHMENT: AUDITOR');
            $pc = $merger->setSourceFile($coverAuditor);
            for ($p = 1; $p <= $pc; $p++) {
                $tplId = $merger->importPage($p);
                $size = $merger->getTemplateSize($tplId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $merger->AddPage($orientation, [$size['width'], $size['height']]);
                $merger->useTemplate($tplId);
            }
            foreach ($auditorPdfFiles as $pf) {
                try {
                    $pc = $merger->setSourceFile($pf);
                    for ($p = 1; $p <= $pc; $p++) {
                        $tplId = $merger->importPage($p);
                        $size = $merger->getTemplateSize($tplId);
                        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                        $merger->AddPage($orientation, [$size['width'], $size['height']]);
                        $merger->useTemplate($tplId);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Failed to append PDF {$pf}: " . $e->getMessage());
                }
            }
            @unlink($coverAuditor);
        }
        if (count($auditeePdfFiles)) {
            $coverAuditee = $generateCoverPdf('ATTACHMENT: AUDITEE');
            $pc = $merger->setSourceFile($coverAuditee);
            for ($p = 1; $p <= $pc; $p++) {
                $tplId = $merger->importPage($p);
                $size = $merger->getTemplateSize($tplId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $merger->AddPage($orientation, [$size['width'], $size['height']]);
                $merger->useTemplate($tplId);
            }
            foreach ($auditeePdfFiles as $pf) {
                try {
                    $pc = $merger->setSourceFile($pf);
                    for ($p = 1; $p <= $pc; $p++) {
                        $tplId = $merger->importPage($p);
                        $size = $merger->getTemplateSize($tplId);
                        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                        $merger->AddPage($orientation, [$size['width'], $size['height']]);
                        $merger->useTemplate($tplId);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Failed to append PDF {$pf}: " . $e->getMessage());
                }
            }
            // Render image attachments from auditee action after auditee cover and PDFs
            $auditeeImageFiles = [];
            if (!empty($finding->auditeeAction) && !empty($finding->auditeeAction->file)) {
                foreach ($finding->auditeeAction->file as $file) {
                    $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                        $auditeeImageFiles[] = $file->full_url ?? $file->file_url ?? $file->file_path;
                    }
                }
            }
            if (count($auditeeImageFiles)) {
                foreach ($auditeeImageFiles as $imgPath) {
                    $imgPdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                        'chroot' => base_path(),
                    ])->loadView('contents.ftpp2.pdf_image', ['imagePath' => $imgPath])->output();
                    $tmpImgPdf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ftpp_img_' . uniqid() . '.pdf';
                    file_put_contents($tmpImgPdf, $imgPdf);
                    $imgPageCount = $merger->setSourceFile($tmpImgPdf);
                    for ($i = 1; $i <= $imgPageCount; $i++) {
                        $tpl = $merger->importPage($i);
                        $merger->AddPage();
                        $merger->useTemplate($tpl);
                    }
                    @unlink($tmpImgPdf);
                }
            }
            @unlink($coverAuditee);
        }

        $tmpMerged = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ftpp_merged_' . uniqid() . '.pdf';
        $merger->Output($tmpMerged, 'F');

        // cleanup main temp
        @unlink($tmpMain);

        return response()->file($tmpMerged);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $finding = AuditFinding::with([
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file',
            'file',
        ])->find($id);
        if (!$finding) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Finding not found'], 404);
            }
            return redirect('/ftpp')->with('error', 'Finding not found');
        }

        try {
            $markedForDeletionAt = now()->addYear();
            // Tandai finding utama
            $finding->marked_for_deletion_at = $markedForDeletionAt;
            $finding->save();

            // Tandai auditee action dan relasi-relasinya jika ada
            if ($finding->auditeeAction) {
                $finding->auditeeAction->marked_for_deletion_at = $markedForDeletionAt;
                $finding->auditeeAction->save();
                // WhyCauses
                foreach ($finding->auditeeAction->whyCauses as $why) {
                    $why->marked_for_deletion_at = $markedForDeletionAt;
                    $why->save();
                }
                // CorrectiveActions
                foreach ($finding->auditeeAction->correctiveActions as $corr) {
                    $corr->marked_for_deletion_at = $markedForDeletionAt;
                    $corr->save();
                }
                // PreventiveActions
                foreach ($finding->auditeeAction->preventiveActions as $prev) {
                    $prev->marked_for_deletion_at = $markedForDeletionAt;
                    $prev->save();
                }
                // Files (auditeeAction)
                foreach ($finding->auditeeAction->file as $file) {
                    $file->marked_for_deletion_at = $markedForDeletionAt;
                    $file->save();
                }
            }
            // Files (finding)
            foreach ($finding->file as $file) {
                $file->marked_for_deletion_at = $markedForDeletionAt;
                $file->save();
            }

            // Tandai AuditFindingAuditee (pivot auditee)
            \App\Models\AuditFindingAuditee::where('audit_finding_id', $finding->id)
                ->update(['marked_for_deletion_at' => $markedForDeletionAt]);

            // Tandai AuditFindingSubKlausul (pivot sub klausul)
            \App\Models\AuditFindingSubKlausul::where('audit_finding_id', $finding->id)
                ->update(['marked_for_deletion_at' => $markedForDeletionAt]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Finding scheduled for deletion in 1 year']);
            }
            return redirect('/ftpp')->with('success', 'Finding scheduled for deletion in 1 year.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Failed to schedule deletion'], 500);
            }
            return redirect('/ftpp')->with('error', 'Failed to schedule deletion.');
        }
    }

    /**
     * Bulk Delete Findings
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tt_audit_findings,id'
        ]);

        try {
            $deleted = AuditFinding::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deleted} finding(s) deleted successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete findings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download PDF Finding
     */
    public function download($id)
    {
        // Ambil data finding beserta relasi
        $finding = AuditFinding::with([
            'audit',
            'subAudit',
            'findingCategory',
            'auditor',
            'auditee',
            'department',
            'process',
            'product',
            'subKlausuls',
            'file',
            'status',
            'auditeeAction',
            'auditeeAction.whyCauses',
            'auditeeAction.correctiveActions',
            'auditeeAction.preventiveActions',
            'auditeeAction.file', // lampiran
        ])->findOrFail($id);

        // Set stamp image URLs when signature/approval flags are set (value == 1)
        if ($finding->auditeeAction) {
            // Dept head signature (show manager approval image)
            if (!empty($finding->auditeeAction->dept_head_signature) && $finding->auditeeAction->dept_head_signature == 1) {
                $finding->dept_head_signature_url = public_path('images/mgr-approve.png');
            }

            // Leader / Supervisor signature (show user approval image)
            if (!empty($finding->auditeeAction->ldr_spv_signature) && $finding->auditeeAction->ldr_spv_signature == 1) {
                $finding->ldr_spv_signature_url = public_path('images/usr-approve.png');
            }

            // Acknowledge by lead auditor: if flag present on finding or auditeeAction, show lead auditor stamp
            if (
                (!empty($finding->acknowledge_by_lead_auditor) && $finding->acknowledge_by_lead_auditor == 1)
                || (!empty($finding->auditeeAction->acknowledge_by_lead_auditor) && $finding->auditeeAction->acknowledge_by_lead_auditor == 1)
            ) {
                $finding->acknowledge_by_lead_auditor_url = public_path('images/stamp-lead-auditor.png');
            }

            // Verified by auditor: if flag present on finding or auditeeAction, show internal auditor stamp
            if (
                (!empty($finding->verified_by_auditor) && $finding->verified_by_auditor == 1)
                || (!empty($finding->auditeeAction->verified_by_auditor) && $finding->auditeeAction->verified_by_auditor == 1)
            ) {
                $finding->verified_by_auditor_url = public_path('images/stamp-internal-auditor.png');
            }
        } else {
            // In case auditeeAction is absent but flags are on the finding
            if (!empty($finding->dept_head_signature) && $finding->dept_head_signature == 1) {
                $finding->dept_head_signature_url = public_path('images/mgr-approve.png');
            }
            if (!empty($finding->ldr_spv_signature) && $finding->ldr_spv_signature == 1) {
                $finding->ldr_spv_signature_url = public_path('images/usr-approve.png');
            }
            if (!empty($finding->acknowledge_by_lead_auditor) && $finding->acknowledge_by_lead_auditor == 1) {
                $finding->acknowledge_by_lead_auditor_url = public_path('images/stamp-lead-auditor.png');
            }
            if (!empty($finding->verified_by_auditor) && $finding->verified_by_auditor == 1) {
                $finding->verified_by_auditor_url = public_path('images/stamp-internal-auditor.png');
            }
        }

        // Tambah filesystem path untuk semua lampiran pada audit finding (images/files) so DomPDF can embed them
        if ($finding->file) {
            foreach ($finding->file as $file) {
                $publicPath = public_path('storage/' . $file->file_path);
                $diskPath = storage_path('app/public/' . $file->file_path);

                if (file_exists($publicPath)) {
                    $file->full_url = $publicPath;
                } elseif (file_exists($diskPath)) {
                    $file->full_url = 'file://' . $diskPath;
                } else {
                    $file->full_url = null;
                }
            }
        }

        // Tambah filesystem path untuk semua lampiran pada auditeeAction (lampiran auditee) so DomPDF can embed them
        if ($finding->auditeeAction && $finding->auditeeAction->file) {
            foreach ($finding->auditeeAction->file as $file) {
                $publicPath = public_path('storage/' . $file->file_path);
                $diskPath = storage_path('app/public/' . $file->file_path);

                if (file_exists($publicPath)) {
                    $file->full_url = $publicPath;
                } elseif (file_exists($diskPath)) {
                    $file->full_url = 'file://' . $diskPath;
                } else {
                    $file->full_url = null;
                }
            }
        }

        // Generate main PDF content
        $pdf = PDF::loadView('contents.ftpp2.pdf', compact('finding'))
            ->setPaper('a4', 'portrait');

        $mainPdfContent = $pdf->output();

        // Attempt to merge attached PDFs (from finding->file and auditeeAction->file)
        $tempDir = sys_get_temp_dir();
        $mainTempPath = $tempDir . DIRECTORY_SEPARATOR . 'ftpp_main_' . uniqid() . '.pdf';
        file_put_contents($mainTempPath, $mainPdfContent);

        // Only merge once: main PDF, then covers+attachments
        $auditorPdfFiles = [];
        if ($finding->file) {
            foreach ($finding->file as $file) {
                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $diskPath = storage_path('app/public/' . $file->file_path);
                    if (file_exists($diskPath)) {
                        $auditorPdfFiles[] = $diskPath;
                    }
                }
            }
        }
        $auditeePdfFiles = [];
        if ($finding->auditeeAction && $finding->auditeeAction->file) {
            foreach ($finding->auditeeAction->file as $file) {
                $ext = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $diskPath = storage_path('app/public/' . $file->file_path);
                    if (file_exists($diskPath)) {
                        $auditeePdfFiles[] = $diskPath;
                    }
                }
            }
        }

        // Use only safe characters for filename (alphanumeric, dash, underscore)
        $safeRegNum = preg_replace('/[^A-Za-z0-9\-_]/', '_', $finding->registration_number);
        $finalFilename = 'FTPP_Finding_' . $safeRegNum . '.pdf';

        if (empty($auditorPdfFiles) && empty($auditeePdfFiles)) {
            // No other PDFs to merge — return main PDF
            // Clean up temp
            @unlink($mainTempPath);
            return response($mainPdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $finalFilename . '"'
            ]);
        }

        if (!class_exists('\\setasign\\Fpdi\\Fpdi')) {
            \Log::warning('FPDI not available. Install it with: composer require setasign/fpdi-fpdf');
            @unlink($mainTempPath);
            return response($mainPdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $finalFilename . '"'
            ]);
        }

        try {
            $merger = new \setasign\Fpdi\Fpdi();
            $pageCount = $merger->setSourceFile($mainTempPath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $merger->importPage($pageNo);
                $size = $merger->getTemplateSize($tplId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $merger->AddPage($orientation, [$size['width'], $size['height']]);
                $merger->useTemplate($tplId);
            }

            // Helper to generate a cover PDF and return its temp path
            $generateCoverPdf = function($coverTitle) {
                $coverHtml = view('contents.ftpp2.pdf_cover', ['coverTitle' => $coverTitle])->render();
                $coverPdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => base_path(),
                ])->loadHTML($coverHtml)->output();
                $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ftpp_cover_' . uniqid() . '.pdf';
                file_put_contents($tmp, $coverPdf);
                return $tmp;
            };

            if (count($auditorPdfFiles)) {
                $coverAuditor = $generateCoverPdf('ATTACHMENT: AUDITOR');
                $pc = $merger->setSourceFile($coverAuditor);
                for ($p = 1; $p <= $pc; $p++) {
                    $tplId = $merger->importPage($p);
                    $size = $merger->getTemplateSize($tplId);
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $merger->AddPage($orientation, [$size['width'], $size['height']]);
                    $merger->useTemplate($tplId);
                }
                foreach ($auditorPdfFiles as $pf) {
                    $pc = $merger->setSourceFile($pf);
                    for ($p = 1; $p <= $pc; $p++) {
                        $tplId = $merger->importPage($p);
                        $size = $merger->getTemplateSize($tplId);
                        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                        $merger->AddPage($orientation, [$size['width'], $size['height']]);
                        $merger->useTemplate($tplId);
                    }
                }
                @unlink($coverAuditor);
            }
            if (count($auditeePdfFiles)) {
                $coverAuditee = $generateCoverPdf('ATTACHMENT: AUDITEE');
                $pc = $merger->setSourceFile($coverAuditee);
                for ($p = 1; $p <= $pc; $p++) {
                    $tplId = $merger->importPage($p);
                    $size = $merger->getTemplateSize($tplId);
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $merger->AddPage($orientation, [$size['width'], $size['height']]);
                    $merger->useTemplate($tplId);
                }
                foreach ($auditeePdfFiles as $pf) {
                    $pc = $merger->setSourceFile($pf);
                    for ($p = 1; $p <= $pc; $p++) {
                        $tplId = $merger->importPage($p);
                        $size = $merger->getTemplateSize($tplId);
                        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                        $merger->AddPage($orientation, [$size['width'], $size['height']]);
                        $merger->useTemplate($tplId);
                    }
                }
                // Render image attachments from auditee action after auditee cover and PDFs
                $auditeeImageFiles = [];
                if (!empty($finding->auditeeAction) && !empty($finding->auditeeAction->file)) {
                    foreach ($finding->auditeeAction->file as $file) {
                        $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                            $auditeeImageFiles[] = $file->full_url ?? $file->file_url ?? $file->file_path;
                        }
                    }
                }
                if (count($auditeeImageFiles)) {
                    foreach ($auditeeImageFiles as $imgPath) {
                        $imgPdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions([
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true,
                            'chroot' => base_path(),
                        ])->loadView('contents.ftpp2.pdf_image', ['imagePath' => $imgPath])->output();
                        $tmpImgPdf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ftpp_img_' . uniqid() . '.pdf';
                        file_put_contents($tmpImgPdf, $imgPdf);
                        $imgPageCount = $merger->setSourceFile($tmpImgPdf);
                        for ($i = 1; $i <= $imgPageCount; $i++) {
                            $tpl = $merger->importPage($i);
                            $merger->AddPage();
                            $merger->useTemplate($tpl);
                        }
                        @unlink($tmpImgPdf);
                    }
                }
                @unlink($coverAuditee);
            }

            $mergedPdfString = $merger->Output('', 'S');
            @unlink($mainTempPath);
            return response($mergedPdfString, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $finalFilename . '"'
            ]);
        } catch (\Throwable $e) {
            \Log::error('PDF merge failed: ' . $e->getMessage());
            @unlink($mainTempPath);
            return response($mainPdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $finalFilename . '"'
            ]);
        }
    }
}
