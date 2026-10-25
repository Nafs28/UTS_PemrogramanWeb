<?php
/**
 * build_uts.php
 * Generator Lengkap Proyek Terpisah UTS: inventory-sales (Toko Kopi Nusantara)
 * Mencakup 100% syarat Slide 1 - 28 PPT UTS Web Programming 2
 */

$base = __DIR__;

function putFile($path, $content) {
    $full = __DIR__ . '/' . $path;
    if (!is_dir(dirname($full))) {
        mkdir(dirname($full), 0777, true);
    }
    file_put_contents($full, $content);
    echo "✅ Dibuat: $path\n";
}

// 1. Buat file .env bersih yang mengarah ke database terpisah: inventory_sales_db
putFile('.env', <<<'EOD'
APP_NAME="Kopi Nusantara Inventory & Sales"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://127.0.0.1:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_sales_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file
EOD
);

// 2. Bersihkan folder migrations & buat 1 file migration terstruktur sesuai Slide 9 PPT UTS
foreach (glob("$base/database/migrations/*.php") as $f) {
    unlink($f);
}

putFile('database/migrations/2026_10_25_000000_create_uts_schema_tables.php', <<<'EOD'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. users (id, name, email unique, password, role)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'staff'])->default('staff')->index();
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. categories (id, name unique)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        // 3. suppliers (id, name, phone, email)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 50);
            $table->string('email', 100)->nullable();
            $table->timestamps();
        });

        // 4. products (id, category_id FK, sku unique, name, purchase_price, selling_price, stock, minimum_stock)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('name', 150)->index();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->timestamps();
        });

        // 5. purchases (id, supplier_id FK, user_id FK, purchase_number unique, total, status, purchased_at)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('purchase_number', 50)->unique();
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('status', ['completed', 'cancelled'])->default('completed')->index();
            $table->timestamp('purchased_at')->index();
            $table->timestamps();
        });

        // 6. purchase_items (id, purchase_id FK, product_id FK, quantity, unit_cost, subtotal)
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });

        // 7. sales (id, user_id FK, sale_number unique, total, status, sold_at)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('sale_number', 50)->unique();
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('status', ['completed', 'cancelled'])->default('completed')->index();
            $table->timestamp('sold_at')->index();
            $table->timestamps();
        });

        // 8. sale_items (id, sale_id FK, product_id FK, quantity, unit_price, subtotal)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};
EOD
);

// 3. Buat 8 Models Eloquent sesuai Slide 10 PPT UTS
putFile('app/Models/User.php', <<<'EOD'
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
EOD
);

putFile('app/Models/Category.php', <<<'EOD'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
EOD
);

putFile('app/Models/Supplier.php', <<<'EOD'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'email'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
EOD
);

putFile('app/Models/Product.php', <<<'EOD'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'purchase_price',
        'selling_price',
        'stock',
        'minimum_stock',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
EOD
);

putFile('app/Models/Purchase.php', <<<'EOD'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'user_id',
        'purchase_number',
        'total',
        'status',
        'purchased_at',
    ];

    protected $casts = ['purchased_at' => 'datetime'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
EOD
);

putFile('app/Models/PurchaseItem.php', <<<'EOD'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'product_id', 'quantity', 'unit_cost', 'subtotal'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
EOD
);

putFile('app/Models/Sale.php', <<<'EOD'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['user_id', 'sale_number', 'total', 'status', 'sold_at'];

    protected $casts = ['sold_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
EOD
);

putFile('app/Models/SaleItem.php', <<<'EOD'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
EOD
);

// 4. AppServiceProvider: Gate Authorization (Slide 5 & 12 PPT)
putFile('app/Providers/AppServiceProvider.php', <<<'EOD'
<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::define('admin-only', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-transactions', function (User $user) {
            return in_array($user->role, ['admin', 'staff']);
        });
    }
}
EOD
);

// 5. Buat Controllers Lengkap (Auth, User, Dashboard, Category, Supplier, Product, Purchase, Sale)
putFile('app/Http/Controllers/AuthController.php', <<<'EOD'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Login berhasil sebagai ' . auth()->user()->name . ' (' . strtoupper(auth()->user()->role) . ').');
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak valid.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah keluar dari sistem.');
    }
}
EOD
);

putFile('app/Http/Controllers/UserController.php', <<<'EOD'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('admin-only');
        $users = User::withCount(['sales', 'purchases'])->orderBy('role')->orderBy('name')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,staff'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('admin-only');
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri yang sedang aktif.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,staff'],
        ]);

        $user->update(['role' => $validated['role']]);
        return redirect()->route('users.index')->with('success', "Role untuk {$user->name} berhasil diubah menjadi " . strtoupper($validated['role']) . ".");
    }
}
EOD
);

putFile('app/Http/Controllers/DashboardController.php', <<<'EOD'
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();

        $salesToday = Sale::where('status', 'completed')->whereDate('sold_at', today())->sum('total');
        $salesThisMonth = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total');

        $purchasesThisMonth = Purchase::where('status', 'completed')
            ->whereMonth('purchased_at', now()->month)
            ->whereYear('purchased_at', now()->year)
            ->sum('total');

        $totalSalesCount = Sale::where('status', 'completed')->count();
        $totalPurchasesCount = Purchase::where('status', 'completed')->count();

        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock', 'asc')
            ->get();

        $recentSales = Sale::with('user')->latest('sold_at')->take(5)->get();
        $recentPurchases = Purchase::with(['supplier', 'user'])->latest('purchased_at')->take(5)->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalSuppliers',
            'salesToday',
            'salesThisMonth',
            'purchasesThisMonth',
            'totalSalesCount',
            'totalPurchasesCount',
            'lowStockProducts',
            'recentSales',
            'recentPurchases'
        ));
    }
}
EOD
);

putFile('app/Http/Controllers/CategoryController.php', <<<'EOD'
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index()
    {
        Gate::authorize('admin-only');
        $categories = Category::withCount('products')->orderBy('name')->paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        Category::create($validated);
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        Gate::authorize('admin-only');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
        ]);

        $category->update($validated);
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        Gate::authorize('admin-only');
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
EOD
);

putFile('app/Http/Controllers/SupplierController.php', <<<'EOD'
<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    public function index()
    {
        Gate::authorize('admin-only');
        $suppliers = Supplier::withCount('purchases')->orderBy('name')->paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);

        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        Gate::authorize('admin-only');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);

        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        Gate::authorize('admin-only');
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Supplier tidak dapat dihapus karena memiliki riwayat transaksi pembelian.');
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
EOD
);

putFile('app/Http/Controllers/ProductController.php', <<<'EOD'
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Eager loading with('category') menghindari N+1 Query (Slide 10 PPT)
        $query = Product::with('category');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status') && $request->stock_status === 'low') {
            $query->whereColumn('stock', '<=', 'minimum_stock');
        }

        $products = $query->orderBy('name')->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function lowStock()
    {
        $products = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock', 'asc')
            ->paginate(10);

        return view('products.low_stock', compact('products'));
    }

    public function create()
    {
        Gate::authorize('admin-only');
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');
        // Sesuai Slide 15 PPT UTS
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ]);

        Product::create($validated);
        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'purchaseItems.purchase.supplier', 'saleItems.sale.user']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        Gate::authorize('admin-only');
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        Gate::authorize('admin-only');
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        Gate::authorize('admin-only');
        // Slide 8 PPT: Produk yang sudah dipakai dalam transaksi tidak dihapus secara hard delete
        if ($product->purchaseItems()->exists() || $product->saleItems()->exists()) {
            return back()->with('error', 'Pelanggaran Business Rule: Produk sudah digunakan dalam transaksi, dilarang melakukan Hard Delete!');
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
EOD
);

putFile('app/Http/Controllers/PurchaseController.php', <<<'EOD'
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user'])->withCount('items');

        if ($request->filled('search')) {
            $query->where('purchase_number', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('purchased_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchased_at', '<=', $request->end_date);
        }

        $purchases = $query->latest('purchased_at')->paginate(10)->withQueryString();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'purchase_number' => 'PO-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                if ($qty < 1 || $unitCost < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Kuantitas harus > 0 dan harga beli tidak boleh negatif.',
                    ]);
                }

                $subtotal = $unitCost * $qty;

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $qty);
                $product->update(['purchase_price' => $unitCost]);
                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);
        });

        return redirect()->route('purchases.index')->with('success', 'Pembelian (Stock-In) berhasil disimpan dan stok produk telah bertambah.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase)
    {
        Gate::authorize('admin-only');
        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'Transaksi sudah dibatalkan sebelumnya.');
        }

        try {
            DB::transaction(function () use ($purchase) {
                $purchase->load('items');
                foreach ($purchase->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    if ($product->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'cancel' => "Pembatalan ditolak! Stok {$product->name} saat ini ({$product->stock}) kurang dari jumlah pembelian yang akan ditarik ({$item->quantity}).",
                        ]);
                    }
                    $product->decrement('stock', $item->quantity);
                }
                $purchase->update(['status' => 'cancelled']);
            });
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Transaksi pembelian dibatalkan dan stok produk telah disesuaikan kembali.');
    }
}
EOD
);

putFile('app/Http/Controllers/SaleController.php', <<<'EOD'
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('user')->withCount('items');

        if ($request->filled('search')) {
            $query->where('sale_number', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('sold_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sold_at', '<=', $request->end_date);
        }

        $sales = $query->latest('sold_at')->paginate(10)->withQueryString();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Persis seperti contoh kode Slide 16 PPT UTS
        DB::transaction(function () use ($request) {
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'sale_number' => $this->generateSaleNumber(),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Transaksi ditolak! Stok '{$product->name}' tidak mencukupi (Tersedia: {$product->stock}, Diminta: {$qty}). Seluruh transaksi di-rollback.",
                    ]);
                }

                $subtotal = $product->selling_price * $qty;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);
        });

        return redirect()->route('sales.index')->with('success', 'Penjualan berhasil disimpan dan stok produk otomatis berkurang.');
    }

    private function generateSaleNumber(): string
    {
        return 'INV-' . now()->format('YmdHis') . '-' . rand(100, 999);
    }

    public function show(Sale $sale)
    {
        $sale->load(['user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        Gate::authorize('admin-only');
        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Penjualan ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($sale) {
            $sale->load('items');
            foreach ($sale->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock', $item->quantity);
            }
            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Transaksi penjualan dibatalkan dan stok produk telah dikembalikan secara konsisten.');
    }
}
EOD
);

// 6. Buat Routes (routes/web.php)
putFile('routes/web.php', <<<'EOD'
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Low-stock dedicated page (Slide 13 PPT)
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low_stock');
    Route::resource('products', ProductController::class);

    // Admin-only Master Data & User Role Management (Slide 5 & 13 PPT)
    Route::middleware('can:admin-only')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('suppliers', SupplierController::class)->except(['create', 'show', 'edit']);
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update_role');
    });

    // Purchases & Sales (Slide 6 & 14 PPT)
    Route::middleware('can:manage-transactions')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    });

    // Cancel Transaction hanya untuk Admin (Slide 5 & 8 PPT)
    Route::middleware('can:admin-only')->group(function () {
        Route::patch('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::patch('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    });
});
EOD
);

// 7. Buat DatabaseSeeder (Data Realistis Toko Kopi Nusantara)
putFile('database/seeders/DatabaseSeeder.php', <<<'EOD'
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Owner Kopi Nusantara',
            'email' => 'admin@kopinusantara.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $staff = User::create([
            'name' => 'Staf Kasir & Gudang',
            'email' => 'staff@kopinusantara.test',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        $catArabika = Category::create(['name' => 'Biji Kopi Arabika']);
        $catRobusta = Category::create(['name' => 'Biji Kopi Robusta']);
        $catSyrup   = Category::create(['name' => 'Sirup & Bahan Campuran']);
        $catAlat    = Category::create(['name' => 'Alat Seduh Manual']);

        $supGayo = Supplier::create([
            'name' => 'Koperasi Petani Kopi Gayo Aceh',
            'phone' => '08116700123',
            'email' => 'order@kopigayo.co.id',
        ]);

        $supRoastery = Supplier::create([
            'name' => 'PT Nusantara Roastery Supply',
            'phone' => '08123456789',
            'email' => 'supply@nusantararoastery.id',
        ]);

        $p1 = Product::create([
            'category_id' => $catArabika->id,
            'sku' => 'KOPI-ARB-001',
            'name' => 'Arabika Aceh Gayo Semi-Wash 250g',
            'purchase_price' => 65000,
            'selling_price' => 95000,
            'stock' => 25,
            'minimum_stock' => 10,
        ]);

        $p2 = Product::create([
            'category_id' => $catArabika->id,
            'sku' => 'KOPI-ARB-002',
            'name' => 'Arabika Toraja Sapan Full-Wash 250g',
            'purchase_price' => 70000,
            'selling_price' => 105000,
            'stock' => 4, // Low stock!
            'minimum_stock' => 8,
        ]);

        $p3 = Product::create([
            'category_id' => $catRobusta->id,
            'sku' => 'KOPI-ROB-001',
            'name' => 'Robusta Dampit Fine Grade 500g',
            'purchase_price' => 55000,
            'selling_price' => 80000,
            'stock' => 30,
            'minimum_stock' => 10,
        ]);

        $p4 = Product::create([
            'category_id' => $catSyrup->id,
            'sku' => 'SYR-VAN-001',
            'name' => 'Sirup Vanilla Gourmet Barista 750ml',
            'purchase_price' => 90000,
            'selling_price' => 135000,
            'stock' => 3, // Low stock!
            'minimum_stock' => 5,
        ]);

        $p5 = Product::create([
            'category_id' => $catAlat->id,
            'sku' => 'EQP-V60-001',
            'name' => 'Dripper Hario V60 Ceramic 02',
            'purchase_price' => 120000,
            'selling_price' => 185000,
            'stock' => 12,
            'minimum_stock' => 5,
        ]);

        // Sample Purchase (Stock-In)
        $po = Purchase::create([
            'supplier_id' => $supGayo->id,
            'user_id' => $admin->id,
            'purchase_number' => 'PO-20261025-001',
            'total' => 1300000,
            'status' => 'completed',
            'purchased_at' => now()->subDays(1),
        ]);
        $po->items()->create([
            'product_id' => $p1->id,
            'quantity' => 20,
            'unit_cost' => 65000,
            'subtotal' => 1300000,
        ]);

        // Sample Sale (Stock-Out)
        $sale = Sale::create([
            'user_id' => $staff->id,
            'sale_number' => 'INV-20261025-001',
            'total' => 350000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);
        $sale->items()->create([
            'product_id' => $p1->id,
            'quantity' => 2,
            'unit_price' => 95000,
            'subtotal' => 190000,
        ]);
        $sale->items()->create([
            'product_id' => $p3->id,
            'quantity' => 2,
            'unit_price' => 80000,
            'subtotal' => 160000,
        ]);
    }
}
EOD
);

// 8. Buat Views Blade Lengkap (Termasuk Low-Stock Page & User Management Page sesuai Slide 13 PPT)
putFile('resources/views/layouts/app.blade.php', <<<'EOD'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inventory & Sales') — Toko Kopi Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">☕ Kopi Nusantara (UTS)</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        @auth
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.index') ? 'active' : '' }}" href="{{ route('products.index') }}">Produk</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.low_stock') ? 'active text-warning fw-semibold' : '' }}" href="{{ route('products.low_stock') }}">⚠️ Stok Rendah</a></li>
                @can('admin-only')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Kategori</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Supplier</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Users & Role</a></li>
                @endcan
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">Purchases (In)</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">Sales (Out)</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3 text-white">
                <div class="text-end">
                    <div class="small fw-semibold">{{ auth()->user()->name }}</div>
                    <span class="badge {{ auth()->user()->isAdmin() ? 'bg-warning text-dark' : 'bg-info text-dark' }}">{{ strtoupper(auth()->user()->role) }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>

<div class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <strong>Validasi Gagal:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
EOD
);

putFile('resources/views/auth/login.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white text-center py-3">
                <h5 class="mb-0">☕ Inventory & Sales Management</h5>
                <small class="text-secondary">UTS Web Programming 2 — Toko Kopi Nusantara</small>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', 'admin@kopinusantara.test') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" value="password" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold">Login</button>
                </form>
                <hr>
                <div class="small bg-light p-3 rounded border">
                    <strong>Akun Demo (Password: <code>password</code>):</strong>
                    <div>🔑 Admin/Owner: <code>admin@kopinusantara.test</code></div>
                    <div>👤 Staff: <code>staff@kopinusantara.test</code></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
EOD
);

putFile('resources/views/dashboard.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Dashboard Inventory & Sales</h3>
        <p class="text-muted mb-0">Ringkasan agregat real-time dari database Toko Kopi Nusantara</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.create') }}" class="btn btn-outline-primary">+ Purchase (Stock-In)</a>
        <a href="{{ route('sales.create') }}" class="btn btn-success">+ Sales (Stock-Out)</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Total Produk</h6>
                <h3 class="fw-bold">{{ $totalProducts }}</h3>
                <small class="text-muted">{{ $totalCategories }} Kategori • {{ $totalSuppliers }} Supplier</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Penjualan Hari Ini</h6>
                <h3 class="fw-bold text-success">Rp {{ number_format($salesToday, 0, ',', '.') }}</h3>
                <small class="text-muted">Bulan ini: Rp {{ number_format($salesThisMonth, 0, ',', '.') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Jumlah Transaksi</h6>
                <h3 class="fw-bold text-primary">{{ $totalSalesCount + $totalPurchasesCount }}</h3>
                <small class="text-muted">{{ $totalSalesCount }} Sales • {{ $totalPurchasesCount }} Purchases</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
            <div class="card-body">
                <h6 class="text-danger">Produk Stok Rendah</h6>
                <h3 class="fw-bold text-danger">{{ $lowStockProducts->count() }}</h3>
                <a href="{{ route('products.low_stock') }}" class="small text-danger">Lihat Daftar Restock →</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold text-danger">⚠️ Peringatan Stok Rendah (Stock &le; Minimum)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>SKU</th><th>Produk</th><th>Stok</th><th>Min</th></tr></thead>
                    <tbody>
                        @forelse($lowStockProducts as $p)
                        <tr>
                            <td><code>{{ $p->sku }}</code></td>
                            <td><a href="{{ route('products.show', $p) }}" class="text-decoration-none fw-semibold">{{ $p->name }}</a></td>
                            <td><span class="badge bg-danger">{{ $p->stock }}</span></td>
                            <td>{{ $p->minimum_stock }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">Semua stok aman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">🛒 Penjualan Terakhir</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>No. Nota</th><th>Staff</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentSales as $s)
                        <tr>
                            <td><a href="{{ route('sales.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->sale_number }}</a></td>
                            <td>{{ $s->user->name }}</td>
                            <td>Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $s->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($s->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
EOD
);

putFile('resources/views/products/low_stock.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Produk Stok Rendah')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-danger mb-0">⚠️ Daftar Produk Stok Rendah (Low-Stock List)</h4>
        <small class="text-muted">Menampilkan produk yang stoknya berada di bawah atau sama dengan batas minimum</small>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Restock via Purchase (Stock-In)</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>SKU</th><th>Nama Produk</th><th>Kategori</th><th>Stok Tersedia</th><th>Minimum Stok</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td><code>{{ $product->sku }}</code></td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td><span class="badge bg-danger fs-6">{{ $product->stock }}</span></td>
                    <td>{{ $product->minimum_stock }}</td>
                    <td><a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">Seluruh produk memiliki stok di atas batas minimum.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection
EOD
);

putFile('resources/views/users/index.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Manajemen Pengguna & Role')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white fw-semibold">Tambah Pengguna Baru (Admin Only)</div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="staff">Staff (Kasir / Gudang)</option>
                            <option value="admin">Admin / Owner</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Simpan Pengguna</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Pengguna & Pengaturan Role</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Nama & Email</th><th>Transaksi</th><th>Role Saat Ini</th><th>Ubah Role</th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $u->name }}</div>
                                <small class="text-muted">{{ $u->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $u->sales_count }} Sales</span>
                                <span class="badge bg-light text-dark border">{{ $u->purchases_count }} PO</span>
                            </td>
                            <td>
                                <span class="badge {{ $u->role === 'admin' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td>
                                @if($u->id !== auth()->id())
                                <form action="{{ route('users.update_role', $u) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="form-select form-select-sm" style="width:110px;">
                                        <option value="staff" {{ $u->role === 'staff' ? 'selected' : '' }}>STAFF</option>
                                        <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>ADMIN</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Update</button>
                                </form>
                                @else
                                <small class="text-muted fst-italic">Akun Anda</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
EOD
);

// Salin tampilan produk, kategori, supplier, purchase, dan sales dari struktur UTS
putFile('resources/views/products/index.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Daftar Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Master Data Produk</h4>
    @can('admin-only')
        <a href="{{ route('products.create') }}" class="btn btn-dark">+ Tambah Produk</a>
    @endcan
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET" class="row g-2">
            <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Cari SKU atau nama produk..." value="{{ request('search') }}"></div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_status" class="form-select">
                    <option value="">-- Semua Status Stok --</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Stok Rendah (&le; Min)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>SKU</th><th>Nama Produk</th><th>Kategori</th><th>Harga Beli</th><th>Harga Jual</th><th>Stok</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td><code>{{ $product->sku }}</code></td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    <td class="fw-semibold text-success">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $product->stock <= $product->minimum_stock ? 'bg-danger' : 'bg-success' }}">
                            {{ $product->stock }} (Min: {{ $product->minimum_stock }})
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        @can('admin-only')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection
EOD
);

putFile('resources/views/products/create.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Tambah Produk')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">Tambah Produk Baru</div>
            <div class="card-body p-4">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">SKU (Unik)</label><input type="text" name="sku" class="form-control" value="{{ old('sku') }}" required></div>
                        <div class="col-md-8"><label class="form-label">Nama Produk</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                        <div class="col-md-12">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Harga Beli (Rp)</label><input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', 0) }}" min="0" required></div>
                        <div class="col-md-6"><label class="form-label">Harga Jual (Rp)</label><input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', 0) }}" min="0" required></div>
                        <div class="col-md-6"><label class="form-label">Stok Awal</label><input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required></div>
                        <div class="col-md-6"><label class="form-label">Minimum Stok</label><input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', 5) }}" min="0" required></div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-dark">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
EOD
);

putFile('resources/views/products/edit.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Edit Produk')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">Edit Produk: {{ $product->name }}</div>
            <div class="card-body p-4">
                <form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">SKU</label><input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required></div>
                        <div class="col-md-8"><label class="form-label">Nama Produk</label><input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required></div>
                        <div class="col-md-12">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Harga Beli (Rp)</label><input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', $product->purchase_price) }}" min="0" required></div>
                        <div class="col-md-4"><label class="form-label">Harga Jual (Rp)</label><input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', $product->selling_price) }}" min="0" required></div>
                        <div class="col-md-4"><label class="form-label">Minimum Stok</label><input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', $product->minimum_stock) }}" min="0" required></div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-dark">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
EOD
);

putFile('resources/views/products/show.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Detail Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Produk: {{ $product->name }}</h4>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p><strong>SKU:</strong> <code>{{ $product->sku }}</code></p>
                <p><strong>Kategori:</strong> {{ $product->category->name }}</p>
                <p><strong>Harga Beli:</strong> Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</p>
                <p><strong>Harga Jual:</strong> Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                <p><strong>Stok Saat Ini:</strong> <span class="badge {{ $product->stock <= $product->minimum_stock ? 'bg-danger' : 'bg-success' }}">{{ $product->stock }}</span></p>
                <p class="mb-0"><strong>Minimum Stok:</strong> {{ $product->minimum_stock }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Riwayat Purchase (Stock-In)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No. PO</th><th>Supplier</th><th>Qty</th><th>Harga Beli</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($product->purchaseItems as $pi)
                        <tr>
                            <td><a href="{{ route('purchases.show', $pi->purchase_id) }}">{{ $pi->purchase->purchase_number }}</a></td>
                            <td>{{ $pi->purchase->supplier->name }}</td>
                            <td class="text-success fw-bold">+{{ $pi->quantity }}</td>
                            <td>Rp {{ number_format($pi->unit_cost, 0, ',', '.') }}</td>
                            <td>{{ strtoupper($pi->purchase->status) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat pembelian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Riwayat Sales (Stock-Out)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No. Invoice</th><th>Kasir</th><th>Qty</th><th>Harga Jual</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($product->saleItems as $si)
                        <tr>
                            <td><a href="{{ route('sales.show', $si->sale_id) }}">{{ $si->sale->sale_number }}</a></td>
                            <td>{{ $si->sale->user->name }}</td>
                            <td class="text-danger fw-bold">-{{ $si->quantity }}</td>
                            <td>Rp {{ number_format($si->unit_price, 0, ',', '.') }}</td>
                            <td>{{ strtoupper($si->sale->status) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat penjualan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
EOD
);

putFile('resources/views/categories/index.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Kategori Produk')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">Tambah Kategori</div>
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3"><label class="form-label">Nama Kategori</label><input type="text" name="name" class="form-control" required></div>
                    <button type="submit" class="btn btn-dark w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Kategori</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Nama Kategori</th><th>Produk</th><th class="text-end">Hapus</th></tr></thead>
                    <tbody>
                        @foreach($categories as $c)
                        <tr>
                            <td>
                                <form action="{{ route('categories.update', $c) }}" method="POST" class="d-flex gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" class="form-control form-control-sm" value="{{ $c->name }}" required>
                                    <button class="btn btn-sm btn-outline-success">Update</button>
                                </form>
                            </td>
                            <td><span class="badge bg-secondary">{{ $c->products_count }}</span></td>
                            <td class="text-end">
                                <form action="{{ route('categories.destroy', $c) }}" method="POST" onsubmit="return confirm('Hapus kategori?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
EOD
);

putFile('resources/views/suppliers/index.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Data Supplier')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">Tambah Supplier</div>
            <div class="card-body">
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3"><label class="form-label">Nama Supplier</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <button type="submit" class="btn btn-dark w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Supplier</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Data Supplier</th><th>PO</th><th class="text-end">Hapus</th></tr></thead>
                    <tbody>
                        @foreach($suppliers as $s)
                        <tr>
                            <td>
                                <form action="{{ route('suppliers.update', $s) }}" method="POST" class="row g-1">
                                    @csrf @method('PUT')
                                    <div class="col-md-4"><input type="text" name="name" class="form-control form-control-sm" value="{{ $s->name }}" required></div>
                                    <div class="col-md-3"><input type="text" name="phone" class="form-control form-control-sm" value="{{ $s->phone }}" required></div>
                                    <div class="col-md-3"><input type="email" name="email" class="form-control form-control-sm" value="{{ $s->email }}"></div>
                                    <div class="col-md-2"><button class="btn btn-sm btn-outline-success w-100">Update</button></div>
                                </form>
                            </td>
                            <td><span class="badge bg-info text-dark">{{ $s->purchases_count }}</span></td>
                            <td class="text-end">
                                <form action="{{ route('suppliers.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus supplier?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
EOD
);

putFile('resources/views/purchases/index.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Daftar Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Transaksi Pembelian (Purchase / Stock-In)</h4>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Buat Purchase Baru</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('purchases.index') }}" method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Cari No. PO..." value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Status --</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}"></div>
            <div class="col-md-2"><input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}"></div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-dark w-100">Filter</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>No. PO</th><th>Tanggal</th><th>Supplier</th><th>Staff</th><th>Item</th><th>Total</th><th>Status</th><th class="text-end">Detail</th></tr>
            </thead>
            <tbody>
                @forelse($purchases as $po)
                <tr>
                    <td class="fw-bold">{{ $po->purchase_number }}</td>
                    <td>{{ $po->purchased_at->format('d M Y H:i') }}</td>
                    <td>{{ $po->supplier->name }}</td>
                    <td>{{ $po->user->name }}</td>
                    <td>{{ $po->items_count }}</td>
                    <td class="fw-semibold">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $po->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($po->status) }}</span></td>
                    <td class="text-end"><a href="{{ route('purchases.show', $po) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada transaksi pembelian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
        <div class="card-footer bg-white">{{ $purchases->links() }}</div>
    @endif
</div>
@endsection
EOD
);

putFile('resources/views/purchases/create.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Buat Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Buat Transaksi Pembelian (Multi-Item Stock-In)</h4>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>

<form action="{{ route('purchases.store') }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <label class="form-label fw-semibold">Pilih Supplier</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">-- Pilih Supplier --</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Item Pembelian</span>
            <button type="button" id="addPoRow" class="btn btn-sm btn-warning fw-semibold">+ Tambah Item</button>
        </div>
        <div class="card-body">
            <table class="table align-middle" id="poTable">
                <thead><tr><th>Produk</th><th>Quantity</th><th>Harga Beli Satuan (Rp)</th><th></th></tr></thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">[{{ $p->sku }}] {{ $p->name }} (Stok: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required></td>
                        <td><input type="number" name="items[0][unit_cost]" class="form-control" value="50000" min="0" required></td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm rm-row">✕</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-end">
            <button type="submit" class="btn btn-primary px-4">Simpan Pembelian & Tambah Stok</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
let idx = 1;
document.getElementById('addPoRow').addEventListener('click', () => {
    const tbody = document.querySelector('#poTable tbody');
    const tr = tbody.querySelector('tr').cloneNode(true);
    tr.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${idx}]`);
    });
    tbody.appendChild(tr);
    idx++;
});
document.addEventListener('click', e => {
    if (e.target.classList.contains('rm-row')) {
        const tbody = document.querySelector('#poTable tbody');
        if (tbody.querySelectorAll('tr').length > 1) e.target.closest('tr').remove();
    }
});
</script>
@endpush
@endsection
EOD
);

putFile('resources/views/purchases/show.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Detail PO')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Purchase: {{ $purchase->purchase_number }}</h4>
    <div class="d-flex gap-2">
        @can('admin-only')
            @if($purchase->status === 'completed')
            <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('Batalkan transaksi pembelian ini?')">
                @csrf @method('PATCH')
                <button class="btn btn-danger">Batalkan Transaksi</button>
            </form>
            @endif
        @endcan
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">← Kembali</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body row">
        <div class="col-md-4"><strong>Supplier:</strong> {{ $purchase->supplier->name }}</div>
        <div class="col-md-3"><strong>Petugas:</strong> {{ $purchase->user->name }}</div>
        <div class="col-md-3"><strong>Waktu:</strong> {{ $purchase->purchased_at->format('d M Y H:i') }}</div>
        <div class="col-md-2"><strong>Status:</strong> <span class="badge {{ $purchase->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($purchase->status) }}</span></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead class="table-light"><tr><th>SKU</th><th>Produk</th><th>Qty</th><th>Unit Cost</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr>
                <td><code>{{ $item->product->sku }}</code></td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light">
            <tr><th colspan="4" class="text-end">Total:</th><th class="text-end fs-5">Rp {{ number_format($purchase->total, 0, ',', '.') }}</th></tr>
        </tfoot>
    </table>
</div>
@endsection
EOD
);

putFile('resources/views/sales/index.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Daftar Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Transaksi Penjualan (Sales / Stock-Out)</h4>
    <a href="{{ route('sales.create') }}" class="btn btn-success">+ Buat Penjualan Baru</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('sales.index') }}" method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Cari No. Invoice..." value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Status --</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}"></div>
            <div class="col-md-2"><input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}"></div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-dark w-100">Filter</button>
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>No. Invoice</th><th>Tanggal</th><th>Staff</th><th>Item</th><th>Total</th><th>Status</th><th class="text-end">Detail</th></tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                <tr>
                    <td class="fw-bold">{{ $s->sale_number }}</td>
                    <td>{{ $s->sold_at->format('d M Y H:i') }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td>{{ $s->items_count }}</td>
                    <td class="fw-semibold text-success">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $s->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($s->status) }}</span></td>
                    <td class="text-end"><a href="{{ route('sales.show', $s) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada transaksi penjualan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
        <div class="card-footer bg-white">{{ $sales->links() }}</div>
    @endif
</div>
@endsection
EOD
);

putFile('resources/views/sales/create.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Buat Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Buat Transaksi Penjualan (Multi-Item Stock-Out)</h4>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>

<form action="{{ route('sales.store') }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Daftar Item Penjualan</span>
            <button type="button" id="addSaleRow" class="btn btn-sm btn-warning fw-semibold">+ Tambah Item</button>
        </div>
        <div class="card-body">
            <table class="table align-middle" id="saleTable">
                <thead><tr><th>Produk (Harga Jual & Stok Tersedia)</th><th>Quantity</th><th></th></tr></thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">[{{ $p->sku }}] {{ $p->name }} — Rp {{ number_format($p->selling_price, 0, ',', '.') }} (Stok: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required></td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm rm-sale-row">✕</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-end">
            <button type="submit" class="btn btn-success px-4">Simpan Penjualan & Kurangi Stok</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
let sIdx = 1;
document.getElementById('addSaleRow').addEventListener('click', () => {
    const tbody = document.querySelector('#saleTable tbody');
    const tr = tbody.querySelector('tr').cloneNode(true);
    tr.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${sIdx}]`);
    });
    tbody.appendChild(tr);
    sIdx++;
});
document.addEventListener('click', e => {
    if (e.target.classList.contains('rm-sale-row')) {
        const tbody = document.querySelector('#saleTable tbody');
        if (tbody.querySelectorAll('tr').length > 1) e.target.closest('tr').remove();
    }
});
</script>
@endpush
@endsection
EOD
);

putFile('resources/views/sales/show.blade.php', <<<'EOD'
@extends('layouts.app')
@section('title', 'Detail Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Invoice: {{ $sale->sale_number }}</h4>
    <div class="d-flex gap-2">
        @can('admin-only')
            @if($sale->status === 'completed')
            <form action="{{ route('sales.cancel', $sale) }}" method="POST" onsubmit="return confirm('Batalkan penjualan ini dan kembalikan stok?')">
                @csrf @method('PATCH')
                <button class="btn btn-danger">Batalkan Penjualan (Pulihkan Stok)</button>
            </form>
            @endif
        @endcan
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">← Kembali</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body row">
        <div class="col-md-4"><strong>Staff / Kasir:</strong> {{ $sale->user->name }}</div>
        <div class="col-md-4"><strong>Waktu:</strong> {{ $sale->sold_at->format('d M Y H:i') }}</div>
        <div class="col-md-4"><strong>Status:</strong> <span class="badge {{ $sale->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($sale->status) }}</span></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead class="table-light"><tr><th>SKU</th><th>Produk</th><th>Qty</th><th>Harga Jual Satuan</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td><code>{{ $item->product->sku }}</code></td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light">
            <tr><th colspan="4" class="text-end">Total:</th><th class="text-end fs-5 text-success">Rp {{ number_format($sale->total, 0, ',', '.') }}</th></tr>
        </tfoot>
    </table>
</div>
@endsection
EOD
);

echo "\n🎉 Proyek terpisah 'inventory-sales' siap dijalankan!\n";