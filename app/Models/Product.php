<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['category', 'title', 'description', 'About', 'price', 'discount'];

    public function Category()
    {
        return $this->belongsTo(Category::class);
    }


    public function Images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // Get average rating
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    // Get total number of ratings
    public function getTotalRatingsAttribute()
    {
        return $this->ratings()->count();
    }
}
