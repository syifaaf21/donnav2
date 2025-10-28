<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhyCauses extends Model
{
    use HasFactory;

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
