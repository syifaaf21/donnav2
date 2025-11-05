<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DocumentMapping;
use App\Models\Status;
use App\Models\User;
use App\Notifications\DocumentStatusNotification;

class CheckObsoleteDocuments extends Command
{
    protected $signature = 'documents:check-obsolete';
    protected $description = 'Mark documents as Obsolete if obsolete_date <= today and notify users';

    public function handle()
    {
        $obsoleteStatus = Status::firstOrCreate(['name' => 'Obsolete']);

        // Ambil semua dokumen yang statusnya belum Obsolete dan tanggal obsolete <= hari ini
        $toBeObsoleted = DocumentMapping::whereHas('status', fn($q) => $q->where('name', '!=', 'Obsolete'))
            ->whereDate('obsolete_date', '<=', now()->today())
            ->get();

        if ($toBeObsoleted->isEmpty()) {
            $this->info('No documents to mark as obsolete today.');
            return;
        }

        foreach ($toBeObsoleted as $mapping) {

            // Update status jadi Obsolete
            $mapping->update(['status_id' => $obsoleteStatus->id]);

            // Ambil semua user di department dokumen
            $departmentUsers = User::where('department_id', $mapping->department_id)->get();

            // Ambil semua admin
            $adminUsers = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->get();

            // Gabungkan dan hapus duplikat
            $notifiableUsers = $departmentUsers->merge($adminUsers)->unique('id');

            foreach ($notifiableUsers as $user) {
                // Cek apakah notif untuk dokumen ini sudah dikirim hari ini
                $alreadyNotified = $user->notifications()
                    ->where('type', DocumentStatusNotification::class)
                    ->whereDate('created_at', now()->today())
                    ->whereJsonContains('data->message', $mapping->document->name)
                    ->exists();

                if (!$alreadyNotified) {
                    $user->notify(new DocumentStatusNotification(
                        $mapping->document->name,
                        'obsolete',
                        'System'
                    ));
                }
            }

            $this->info("Document '{$mapping->document->name}' marked as Obsolete and notifications sent.");
        }

        $this->info('Obsolete check complete.');
    }
}
