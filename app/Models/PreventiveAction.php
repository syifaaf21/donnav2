<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveAction extends Model
{
    use HasFactory;

    protected $table = 'tt_preventive_actions';

    protected $fillable =
    [
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
