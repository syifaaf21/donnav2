<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhyCauses extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Use project-specific marked_for_deletion_at column for soft deletes.
     */
    const DELETED_AT = 'marked_for_deletion_at';

    protected $table = 'tt_why_causes';

    protected $fillable =
    [
        'auditee_action_id',
        'why_description',
        'cause_description',
    ];

    public function auditeeAction()
    {
        return $this->belongsTo(AuditeeAction::class);
    }
}
