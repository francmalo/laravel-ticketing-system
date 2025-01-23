when i press edit am getting this error message[**Internal Server Error** **Illuminate\Contracts\Container\BindingResolutionException** **Target class [role] does not exist.**
   throw new BindingResolutionException("Target class [$concrete] does not exist.", 0, $e);]


   errors in log [2025-01-21 13:14:18] local.ERROR: Target class [role] does not exist. {"userId":1,"exception":"[object] (Illuminate\\Contracts\\Container\\BindingResolutionException(code: 0): Target class [role] does not exist. at C:\\nginx\\scripts\\laravelprojects\\it-ticket-system\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php:946)
[stacktrace][previous exception] [object] (ReflectionException(code: -1): Class \"role\" does not exist at C:\\nginx\\scripts\\laravelprojects\\it-ticket-system\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php:944)
[stacktrace][2025-01-21 13:14:19] local.ERROR: Target class [role] does not exist. {"userId":1,"exception":"[object] (Illuminate\\Contracts\\Container\\BindingResolutionException(code: 0): Target class [role] does not exist. at C:\\nginx\\scripts\\laravelprojects\\it-ticket-system\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php:946)
[stacktrace]

PS C:\nginx\scripts\laravelprojects\it-ticket-system> php artisan middleware:list

   ERROR  There are no commands defined in the "middleware" namespace.
 why am i getting the errors?

below are some of my code for reference


RoleMiddleware.php
[<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        if (!Auth::user()->role) {
            abort(403, 'User has no assigned role.');
        }

        if (!in_array(Auth::user()->role->name, $roles)) {
            abort(403, 'User does not have the required role.');
        }

        return $next($request);
    }
}

]


Kernel.php[<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \Illuminate\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];
}

]


web.php[<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
require __DIR__.'/auth.php';

// Ticket Routes
Route::middleware(['auth'])->group(function () {
    // Routes accessible by all authenticated users
    Route::get('/', [TicketController::class, 'index'])->name('home');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    // Routes only for Admin and IT Staff
    Route::middleware(['role:Admin,IT Staff'])->group(function () {
        Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
        Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    });



    // Comments route
    Route::post('tickets/{ticket}/comments', [CommentController::class, 'store'])->name('comments.store');
});


]


TicketController[<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;


class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     */

    /**
     * Display a listing of the tickets.
     */
    public function index(): View
    {
        $userRole = Auth::user()->role->name;

        $tickets = match($userRole) {
            'Admin', 'IT Staff' => Ticket::with(['assignedTo', 'user'])->latest()->get(),
            'Employee' => Ticket::where('user_id', Auth::id())->with(['assignedTo', 'user'])->latest()->get(),
            default => collect(),
        };

        return view('tickets.index', compact('tickets'));
    }
    /**
     * Show the form for creating a new ticket.
     */
    public function create(): View
    {
        return view('tickets.create');
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open', // Set default status
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket): View
    {
        // Check if user can view this ticket
        if (Auth::user()->role->name === 'Employee' && $ticket->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->load(['assignedTo', 'user', 'comments.user']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit(Ticket $ticket): View
    {
        // Only Admin and IT Staff can access this (already handled in constructor middleware)
        $users = User::whereHas('role', function($query) {
            $query->whereIn('name', ['Admin', 'IT Staff']);
        })->get();

        return view('tickets.edit', compact('ticket', 'users'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $ticket->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to']
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        // Only Admin and IT Staff can delete tickets (already handled in constructor middleware)
        $ticket->comments()->delete(); // Delete associated comments first
        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Update the ticket status.
     */
    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        if (!in_array(Auth::user()->role->name, ['Admin', 'IT Staff'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $ticket->update(['status' => $validated['status']]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Assign ticket to a staff member.
     */
    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        if (!in_array(Auth::user()->role->name, ['Admin', 'IT Staff'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        $ticket->update(['assigned_to' => $validated['assigned_to']]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket assigned successfully.');
    }
}
]



rolemodel[<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Role extends Model {
    protected $fillable = ['name'];

    public function users() {
        return $this->hasMany(User::class);
    }
}

]

usermodel[<?php

namespace App\Models;


use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }
}
]



roleseeder[<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;  // Add this import

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'Admin'],
            ['name' => 'IT Staff'],
            ['name' => 'Employee'],
        ]);
    }
}
]

userseeder[<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create an Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // Admin role
        ]);

        // Create an IT Staff user
        User::create([
            'name' => 'IT Staff User',
            'email' => 'itstaff@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // IT Staff role
        ]);

        // Create an Employee user
        User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role_id' => 3, // Employee role
        ]);
    }
}


]



my migrations

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
     Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Admin, IT Staff, Employee
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->enum('priority', ['low', 'medium', 'high'])->default('low');
    $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Created by
    $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Assigned IT staff
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
     Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Commented by
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('users', function (Blueprint $table) {
    $table->foreignId('role_id')->constrained()->default(3); // Default: Employee
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};



can spatie laravel permission help and make the error go away and make my code work better

