<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitigationComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id',
        'author_name',
        'message',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }
}
