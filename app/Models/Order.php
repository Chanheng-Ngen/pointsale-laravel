<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
  protected $fillable = [
    'order_number',
    'status',
    'item_count',
    'subtotal',
    'tax',
    'total',
    'payment_method',
    'customer_name',
  ];

  protected $appends = ['time', 'date'];

  public function items()
  {
    return $this->hasMany(OrderItem::class);
  }

  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }

  public function getTimeAttribute()
  {
      return $this->created_at ? $this->created_at->format('h:i A') : '';
  }

  public function getDateAttribute()
  {
      return $this->created_at ? $this->created_at->format('Y-m-d') : '';
  }
}
