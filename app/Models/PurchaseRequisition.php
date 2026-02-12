<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'requisitioner',
        'item_number',
        'short_text',
        'po_number',
        'deletion_flag',
        'gr_indicator',
        'ir_indicator',
        'material',
        'tracking_number',
        'purchasing_group',
        'item_category',
        'account_assignment',
        'release_indicator',
        'release_code',
        'release_date',
        'purchasing_org',
        'supplier',
        'supplied_material',
        'rs_status',
        'req_date',
        'quantity',
        'unit',
        'po_date',
        'po_time',
        'currency',
        'per',
        'total_value',
        'mitigation_reason',
        'mitigation_status',
        'department',
        'po_release_date',
    ];

    protected $casts = [
        'req_date' => 'date',
        'po_date' => 'date',
        'release_date' => 'date',
        'quantity' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function comments()
    {
        return $this->hasMany(MitigationComment::class)->orderBy('created_at', 'asc');
    }
}

