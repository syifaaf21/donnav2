<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrectiveAction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Use project-specific marked_for_deletion_at column for soft deletes.
     */
    const DELETED_AT = 'marked_for_deletion_at';

    protected $table = 'tt_corrective_actions';

    protected $fillable =
    [
        'pic',
        'auditee_action_id',
        'activity',
        'planning_date',
        'actual_date',
    ];

    public function auditeeAction()
    {
        return $this->belongsTo(AuditeeAction::class);
    }
}
