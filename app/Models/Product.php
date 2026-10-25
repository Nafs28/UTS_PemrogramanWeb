<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'price',
        'stock',
        'minimum_stock',
    ];

    // Relasi Belongs-To ke Category (Slide 33)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi Many-to-Many ke Supplier (Kesinambungan P4)
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_suppliers')
                    ->withPivot('supplier_price');
    }

    // Relasi One-to-Many ke Histori Transaksi Stok
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}