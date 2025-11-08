<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectiveAction extends Model
{
    use HasFactory;

    protected $table = 'tt_corrective_actions';

    protected $fillable =
    [
        'user_id',
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
