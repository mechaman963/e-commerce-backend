<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    /**
     * Get the user that owns the cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product associated with the cart item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the total price for this cart item.
     */
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get cart items with product details for a user.
     */
    public static function getCartWithProducts($userId)
    {
        return self::with(['product.images', 'product.category'])
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Get cart summary for a user.
     */
    public static function getCartSummary($userId)
    {
        $cartItems = self::where('user_id', $userId)->get();
        
        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        $totalItems = $cartItems->sum('quantity');
        
        return [
            'subtotal' => $subtotal,
            'total_items' => $totalItems,
            'items_count' => $cartItems->count()
        ];
    }
}
