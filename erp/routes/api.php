<?php

use App\Http\Controllers\Api\V1\AccountingApiController;
use App\Http\Controllers\Api\V1\ImportExportController;
use App\Http\Controllers\Api\V1\AppointmentsApiController;
use App\Http\Controllers\Api\V1\ApprovalsApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CrmApiController;
use App\Http\Controllers\Api\V1\CurrencyApiController;
use App\Http\Controllers\Api\V1\CustomerApiController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\DiscussApiController;
use App\Http\Controllers\Api\V1\DocumentsApiController;
use App\Http\Controllers\Api\V1\EcommerceApiController;
use App\Http\Controllers\Api\V1\EventsApiController;
use App\Http\Controllers\Api\V1\FieldServiceApiController;
use App\Http\Controllers\Api\V1\FinanceApiController;
use App\Http\Controllers\Api\V1\FleetApiController;
use App\Http\Controllers\Api\V1\FrontdeskApiController;
use App\Http\Controllers\Api\V1\HelpdeskApiController;
use App\Http\Controllers\Api\V1\HrApiController;
use App\Http\Controllers\Api\V1\InventoryApiController;
use App\Http\Controllers\Api\V1\InvoiceApiController;
use App\Http\Controllers\Api\V1\KnowledgeBaseApiController;
use App\Http\Controllers\Api\V1\LiveChatApiController;
use App\Http\Controllers\Api\V1\LunchApiController;
use App\Http\Controllers\Api\V1\MaintenanceApiController;
use App\Http\Controllers\Api\V1\ManufacturingApiController;
use App\Http\Controllers\Api\V1\MarketingApiController;
use App\Http\Controllers\Api\V1\PlanningApiController;
use App\Http\Controllers\Api\V1\PmApiController;
use App\Http\Controllers\Api\V1\PosApiController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\PurchaseApiController;
use App\Http\Controllers\Api\V1\QualityControlApiController;
use App\Http\Controllers\Api\V1\RentalApiController;
use App\Http\Controllers\Api\V1\RepairsApiController;
use App\Http\Controllers\Api\V1\SignApiController;
use App\Http\Controllers\Api\V1\SocialMarketingApiController;
use App\Http\Controllers\Api\V1\PdfController;
use App\Http\Controllers\Api\V1\SubcontractingApiController;
use App\Http\Controllers\Api\V1\SubscriptionsApiController;
use App\Http\Controllers\Api\V1\SurveyApiController;
use App\Http\Controllers\Api\V1\WebsiteApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Health checks (public)
    Route::get('health', [\App\Http\Controllers\Api\V1\HealthController::class, 'check']);

    // Auth (public) — stricter rate limit
    Route::middleware('throttle:auth')->group(function () {
        Route::post('auth/login', [AuthController::class, 'login']);
    });

    // Protected
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',     [AuthController::class, 'me']);

        Route::get('dashboard', [DashboardApiController::class, 'index']);

        // ── Core resources (pre-existing) ───────────────────────────────────

        Route::apiResource('products', ProductApiController::class);

        Route::put('invoices/{invoice}/status', [InvoiceApiController::class, 'updateStatus']);
        Route::apiResource('invoices', InvoiceApiController::class)->except(['update']);

        Route::apiResource('customers', CustomerApiController::class);

        Route::get('inventory/stock',     [InventoryApiController::class, 'stock']);
        Route::get('inventory/movements', [InventoryApiController::class, 'movements']);
        Route::post('inventory/adjust',   [InventoryApiController::class, 'adjust']);

        Route::post('crm/leads/{lead}/won',  [CrmApiController::class, 'markWon']);
        Route::post('crm/leads/{lead}/lost', [CrmApiController::class, 'markLost']);
        Route::apiResource('crm/leads', CrmApiController::class);

        Route::post('helpdesk/tickets/{ticket}/reply',   [HelpdeskApiController::class, 'reply']);
        Route::post('helpdesk/tickets/{ticket}/resolve', [HelpdeskApiController::class, 'resolve']);
        Route::apiResource('helpdesk/tickets', HelpdeskApiController::class);

        Route::get('hr/employees',      [HrApiController::class, 'employees']);
        Route::get('hr/employees/{id}', [HrApiController::class, 'employee']);
        Route::get('hr/departments',    [HrApiController::class, 'departments']);
        Route::get('hr/leave-requests', [HrApiController::class, 'leaveRequests']);

        Route::put('manufacturing/orders/{order}/status', [ManufacturingApiController::class, 'updateStatus']);
        Route::get('manufacturing/orders/{order}',        [ManufacturingApiController::class, 'show']);
        Route::get('manufacturing/orders',                [ManufacturingApiController::class, 'orders']);
        Route::get('manufacturing/boms',                  [ManufacturingApiController::class, 'boms']);

        Route::get('pos/sessions',                  [PosApiController::class, 'sessions']);
        Route::get('pos/sessions/{session}/orders', [PosApiController::class, 'sessionOrders']);
        Route::post('pos/orders',                   [PosApiController::class, 'createOrder']);
        Route::get('pos/orders/{order}',            [PosApiController::class, 'showOrder']);

        Route::get('currencies',         [CurrencyApiController::class, 'index']);
        Route::get('currencies/convert', [CurrencyApiController::class, 'convert']);

        // ── Phase 9: new module routes ───────────────────────────────────────

        // Finance
        Route::get('finance/bills',           [FinanceApiController::class, 'index']);
        Route::post('finance/bills',          [FinanceApiController::class, 'store']);
        Route::get('finance/bills/{id}',      [FinanceApiController::class, 'show']);
        Route::put('finance/bills/{id}',      [FinanceApiController::class, 'update']);
        Route::delete('finance/bills/{id}',   [FinanceApiController::class, 'destroy']);
        Route::get('finance/contacts',        [FinanceApiController::class, 'contacts']);
        Route::post('finance/contacts',       [FinanceApiController::class, 'storeContact']);

        // Accounting
        Route::get('accounting/journal-entries',      [AccountingApiController::class, 'journalEntries']);
        Route::post('accounting/journal-entries',     [AccountingApiController::class, 'storeJournalEntry']);
        Route::get('accounting/journal-entries/{id}', [AccountingApiController::class, 'showJournalEntry']);
        Route::get('accounting/accounts',             [AccountingApiController::class, 'accounts']);
        Route::post('accounting/accounts',            [AccountingApiController::class, 'storeAccount']);

        // Purchase
        Route::get('purchase/vendors',                        [PurchaseApiController::class, 'vendors']);
        Route::post('purchase/vendors',                       [PurchaseApiController::class, 'storeVendor']);
        Route::get('purchase/rfqs',                           [PurchaseApiController::class, 'rfqs']);
        Route::post('purchase/rfqs',                          [PurchaseApiController::class, 'storeRfq']);
        Route::get('purchase/purchase-orders',                [PurchaseApiController::class, 'purchaseOrders']);
        Route::post('purchase/purchase-orders',               [PurchaseApiController::class, 'storePurchaseOrder']);
        Route::get('purchase/purchase-orders/{id}',           [PurchaseApiController::class, 'showPurchaseOrder']);
        Route::post('purchase/purchase-orders/{id}/confirm',  [PurchaseApiController::class, 'confirmPurchaseOrder']);
        Route::post('purchase/purchase-orders/{id}/receive',  [PurchaseApiController::class, 'receivePurchaseOrder']);

        // Project Management
        Route::get('pm/projects',     [PmApiController::class, 'projects']);
        Route::post('pm/projects',    [PmApiController::class, 'storeProject']);
        Route::get('pm/projects/{id}',[PmApiController::class, 'showProject']);
        Route::get('pm/tasks',        [PmApiController::class, 'tasks']);
        Route::post('pm/tasks',       [PmApiController::class, 'storeTask']);
        Route::get('pm/tasks/{id}',   [PmApiController::class, 'showTask']);
        Route::put('pm/tasks/{id}',   [PmApiController::class, 'updateTask']);

        // Subscriptions
        Route::get('subscriptions/plans',         [SubscriptionsApiController::class, 'plans']);
        Route::post('subscriptions/plans',        [SubscriptionsApiController::class, 'storePlan']);
        Route::get('subscriptions',               [SubscriptionsApiController::class, 'subscriptions']);
        Route::post('subscriptions',              [SubscriptionsApiController::class, 'storeSubscription']);
        Route::get('subscriptions/{id}',          [SubscriptionsApiController::class, 'showSubscription']);
        Route::post('subscriptions/{id}/renew',   [SubscriptionsApiController::class, 'renewSubscription']);
        Route::post('subscriptions/{id}/cancel',  [SubscriptionsApiController::class, 'cancelSubscription']);

        // Repairs
        Route::get('repairs',           [RepairsApiController::class, 'index']);
        Route::get('repairs/{id}',      [RepairsApiController::class, 'show']);
        Route::post('repairs',          [RepairsApiController::class, 'store']);
        Route::put('repairs/{id}',      [RepairsApiController::class, 'update']);
        Route::delete('repairs/{id}',   [RepairsApiController::class, 'destroy']);
        Route::post('repairs/{id}/lines', [RepairsApiController::class, 'addLine']);

        // Maintenance
        Route::get('maintenance/orders',           [MaintenanceApiController::class, 'orders']);
        Route::get('maintenance/orders/{id}',      [MaintenanceApiController::class, 'showOrder']);
        Route::post('maintenance/orders',          [MaintenanceApiController::class, 'storeOrder']);
        Route::get('maintenance/equipment',        [MaintenanceApiController::class, 'equipment']);
        Route::post('maintenance/equipment',       [MaintenanceApiController::class, 'storeEquipment']);
        Route::get('maintenance/plans',            [MaintenanceApiController::class, 'plans']);

        // Fleet
        Route::get('fleet/vehicles',          [FleetApiController::class, 'vehicles']);
        Route::get('fleet/vehicles/{id}',     [FleetApiController::class, 'showVehicle']);
        Route::post('fleet/vehicles',         [FleetApiController::class, 'storeVehicle']);
        Route::get('fleet/assignments',       [FleetApiController::class, 'assignments']);
        Route::post('fleet/assignments',      [FleetApiController::class, 'storeAssignment']);
        Route::get('fleet/fuel-logs',         [FleetApiController::class, 'fuelLogs']);

        // Appointments
        Route::get('appointments/types',      [AppointmentsApiController::class, 'types']);
        Route::get('appointments/slots',      [AppointmentsApiController::class, 'slots']);
        Route::get('appointments',            [AppointmentsApiController::class, 'appointments']);
        Route::get('appointments/{id}',       [AppointmentsApiController::class, 'showAppointment']);
        Route::post('appointments',           [AppointmentsApiController::class, 'storeAppointment']);
        Route::post('appointments/{id}/cancel', [AppointmentsApiController::class, 'cancelAppointment']);

        // Rental
        Route::get('rental',       [RentalApiController::class, 'index']);
        Route::get('rental/{id}',  [RentalApiController::class, 'show']);
        Route::post('rental',      [RentalApiController::class, 'store']);
        Route::put('rental/{id}',  [RentalApiController::class, 'update']);
        Route::delete('rental/{id}', [RentalApiController::class, 'destroy']);

        // Quality Control
        Route::get('quality/inspections',        [QualityControlApiController::class, 'inspections']);
        Route::get('quality/inspections/{id}',   [QualityControlApiController::class, 'showInspection']);
        Route::post('quality/inspections',       [QualityControlApiController::class, 'storeInspection']);
        Route::put('quality/inspections/{id}',   [QualityControlApiController::class, 'updateInspection']);
        Route::get('quality/alerts',             [QualityControlApiController::class, 'alerts']);
        Route::get('quality/checklists',         [QualityControlApiController::class, 'checklists']);

        // Marketing
        Route::get('marketing/campaigns',        [MarketingApiController::class, 'campaigns']);
        Route::get('marketing/campaigns/{id}',   [MarketingApiController::class, 'showCampaign']);
        Route::post('marketing/campaigns',       [MarketingApiController::class, 'storeCampaign']);
        Route::get('marketing/mailing-lists',    [MarketingApiController::class, 'mailingLists']);
        Route::post('marketing/mailing-lists',   [MarketingApiController::class, 'storeMailingList']);
        Route::get('marketing/subscribers',      [MarketingApiController::class, 'subscribers']);

        // Ecommerce
        Route::get('ecommerce/products',     [EcommerceApiController::class, 'storeProducts']);
        Route::get('ecommerce/products/{id}',[EcommerceApiController::class, 'showStoreProduct']);
        Route::get('ecommerce/orders',       [EcommerceApiController::class, 'storeOrders']);
        Route::get('ecommerce/orders/{id}',  [EcommerceApiController::class, 'showStoreOrder']);
        Route::get('ecommerce/categories',   [EcommerceApiController::class, 'categories']);

        // Field Service
        Route::get('field-service/tasks',       [FieldServiceApiController::class, 'tasks']);
        Route::get('field-service/tasks/{id}',  [FieldServiceApiController::class, 'showTask']);
        Route::post('field-service/tasks',      [FieldServiceApiController::class, 'storeTask']);
        Route::put('field-service/tasks/{id}',  [FieldServiceApiController::class, 'updateTask']);

        // Discuss
        Route::get('discuss/channels',   [DiscussApiController::class, 'channels']);
        Route::get('discuss/messages',   [DiscussApiController::class, 'messages']);
        Route::post('discuss/messages',  [DiscussApiController::class, 'storeMessage']);

        // Documents
        Route::get('documents',        [DocumentsApiController::class, 'index']);
        Route::get('documents/{id}',   [DocumentsApiController::class, 'show']);
        Route::post('documents',       [DocumentsApiController::class, 'store']);
        Route::delete('documents/{id}',[DocumentsApiController::class, 'destroy']);

        // Events
        Route::get('events',              [EventsApiController::class, 'index']);
        Route::get('events/{id}',         [EventsApiController::class, 'show']);
        Route::post('events',             [EventsApiController::class, 'store']);
        Route::post('events/{id}/register',[EventsApiController::class, 'register']);

        // Approvals
        Route::get('approvals',              [ApprovalsApiController::class, 'index']);
        Route::get('approvals/{id}',         [ApprovalsApiController::class, 'show']);
        Route::post('approvals',             [ApprovalsApiController::class, 'store']);
        Route::post('approvals/{id}/approve',[ApprovalsApiController::class, 'approve']);
        Route::post('approvals/{id}/reject', [ApprovalsApiController::class, 'reject']);

        // Knowledge Base
        Route::get('knowledge-base/articles',       [KnowledgeBaseApiController::class, 'articles']);
        Route::get('knowledge-base/articles/{id}',  [KnowledgeBaseApiController::class, 'showArticle']);
        Route::post('knowledge-base/articles',      [KnowledgeBaseApiController::class, 'storeArticle']);
        Route::put('knowledge-base/articles/{id}',  [KnowledgeBaseApiController::class, 'updateArticle']);
        Route::get('knowledge-base/categories',     [KnowledgeBaseApiController::class, 'categories']);

        // Live Chat
        Route::get('live-chat/channels',   [LiveChatApiController::class, 'channels']);
        Route::get('live-chat/sessions',   [LiveChatApiController::class, 'sessions']);
        Route::get('live-chat/messages',   [LiveChatApiController::class, 'messages']);
        Route::post('live-chat/messages',  [LiveChatApiController::class, 'storeMessage']);

        // Lunch
        Route::get('lunch/suppliers',          [LunchApiController::class, 'suppliers']);
        Route::get('lunch/products',           [LunchApiController::class, 'products']);
        Route::get('lunch/orders',             [LunchApiController::class, 'orders']);
        Route::post('lunch/orders',            [LunchApiController::class, 'storeOrder']);
        Route::post('lunch/orders/{id}/cancel',[LunchApiController::class, 'cancelOrder']);

        // Planning
        Route::get('planning/shifts',          [PlanningApiController::class, 'shifts']);
        Route::get('planning/shifts/{id}',     [PlanningApiController::class, 'showShift']);
        Route::post('planning/shifts',         [PlanningApiController::class, 'storeShift']);
        Route::get('planning/shifts/{id}/swaps',[PlanningApiController::class, 'swaps']);

        // Sign
        Route::get('sign/documents',              [SignApiController::class, 'documents']);
        Route::get('sign/documents/{id}',         [SignApiController::class, 'showDocument']);
        Route::post('sign/documents',             [SignApiController::class, 'storeDocument']);
        Route::post('sign/documents/{id}/send',   [SignApiController::class, 'sendForSignature']);
        Route::put('sign/documents/{id}/status',  [SignApiController::class, 'updateStatus']);

        // Social Marketing
        Route::get('social-marketing/accounts',           [SocialMarketingApiController::class, 'accounts']);
        Route::get('social-marketing/posts',              [SocialMarketingApiController::class, 'posts']);
        Route::get('social-marketing/posts/{id}',         [SocialMarketingApiController::class, 'showPost']);
        Route::post('social-marketing/posts',             [SocialMarketingApiController::class, 'storePost']);
        Route::post('social-marketing/posts/{id}/publish',[SocialMarketingApiController::class, 'publishPost']);

        // Survey
        Route::get('surveys',              [SurveyApiController::class, 'surveys']);
        Route::get('surveys/{id}',         [SurveyApiController::class, 'showSurvey']);
        Route::post('surveys',             [SurveyApiController::class, 'storeSurvey']);
        Route::post('surveys/{id}/respond',[SurveyApiController::class, 'submitResponse']);

        // Website / CMS
        Route::get('website/pages',          [WebsiteApiController::class, 'pages']);
        Route::get('website/pages/{id}',     [WebsiteApiController::class, 'showPage']);
        Route::post('website/pages',         [WebsiteApiController::class, 'storePage']);
        Route::get('website/blog-posts',     [WebsiteApiController::class, 'blogPosts']);
        Route::get('website/blog-posts/{id}',[WebsiteApiController::class, 'showBlogPost']);
        Route::post('website/blog-posts',    [WebsiteApiController::class, 'storeBlogPost']);
        Route::get('website/menus',          [WebsiteApiController::class, 'menus']);

        // Frontdesk
        Route::get('frontdesk/stations',               [FrontdeskApiController::class, 'stations']);
        Route::get('frontdesk/visitors',               [FrontdeskApiController::class, 'visitors']);
        Route::post('frontdesk/visitors/check-in',     [FrontdeskApiController::class, 'checkIn']);
        Route::post('frontdesk/visitors/{id}/checkout',[FrontdeskApiController::class, 'checkOut']);

        // Subcontracting
        Route::get('subcontracting/orders',      [SubcontractingApiController::class, 'index']);
        Route::get('subcontracting/orders/{id}', [SubcontractingApiController::class, 'show']);
        Route::post('subcontracting/orders',     [SubcontractingApiController::class, 'store']);
        Route::put('subcontracting/orders/{id}', [SubcontractingApiController::class, 'update']);

        // Import / Export
        Route::prefix('export')->group(function () {
            Route::get('/products', [ImportExportController::class, 'exportProducts']);
            Route::get('/contacts', [ImportExportController::class, 'exportContacts']);
            Route::get('/invoices', [ImportExportController::class, 'exportInvoices']);
        });
        Route::prefix('import')->group(function () {
            Route::post('/products', [ImportExportController::class, 'importProducts']);
            Route::post('/contacts', [ImportExportController::class, 'importContacts']);
        });

        // PDF Downloads
        Route::prefix('pdf')->group(function () {
            Route::get('/invoices/{id}',        [PdfController::class, 'invoice']);
            Route::get('/purchase-orders/{id}', [PdfController::class, 'purchaseOrder']);
            Route::get('/payslips/{id}',        [PdfController::class, 'payslip']);
        });

        // Global Search
        Route::get('/search', [\App\Http\Controllers\Api\V1\SearchController::class, 'search']);

        // Audit Logs
        Route::get('/audit-logs', [\App\Http\Controllers\Api\V1\AuditLogController::class, 'index']);

        // Unified Calendar
        Route::get('/calendar', [\App\Http\Controllers\Api\V1\CalendarController::class, 'index']);

        // Activity Feed
        Route::prefix('activity')->group(function () {
            Route::get('/',      [\App\Http\Controllers\Api\V1\ActivityFeedController::class, 'index']);
            Route::get('/stats', [\App\Http\Controllers\Api\V1\ActivityFeedController::class, 'stats']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
            Route::get('/unread-count', [\App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount']);
            Route::post('/{id}/read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markRead']);
            Route::post('/mark-all-read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAllRead']);
        });

        // Budget Management API
        Route::prefix('budgets')->group(function () {
            Route::get('/',                     [\App\Http\Controllers\Api\V1\BudgetApiController::class, 'index']);
            Route::post('/',                    [\App\Http\Controllers\Api\V1\BudgetApiController::class, 'store']);
            Route::get('/{budget}',             [\App\Http\Controllers\Api\V1\BudgetApiController::class, 'show']);
            Route::delete('/{budget}',          [\App\Http\Controllers\Api\V1\BudgetApiController::class, 'destroy']);
            Route::post('/{budget}/activate',   [\App\Http\Controllers\Api\V1\BudgetApiController::class, 'activate']);
            Route::post('/{budget}/close',      [\App\Http\Controllers\Api\V1\BudgetApiController::class, 'close']);
            Route::get('/{budget}/variance',    [\App\Http\Controllers\Api\V1\BudgetApiController::class, 'variance']);
        });

        // Smart Alert Rules
        Route::prefix('alert-rules')->group(function () {
            Route::get('/',                        [\App\Http\Controllers\Api\V1\AlertRuleController::class, 'index']);
            Route::post('/',                       [\App\Http\Controllers\Api\V1\AlertRuleController::class, 'store']);
            Route::get('/{alertRule}',             [\App\Http\Controllers\Api\V1\AlertRuleController::class, 'show']);
            Route::put('/{alertRule}',             [\App\Http\Controllers\Api\V1\AlertRuleController::class, 'update']);
            Route::delete('/{alertRule}',          [\App\Http\Controllers\Api\V1\AlertRuleController::class, 'destroy']);
            Route::post('/{alertRule}/run',        [\App\Http\Controllers\Api\V1\AlertRuleController::class, 'run']);
            Route::get('/{alertRule}/events',      [\App\Http\Controllers\Api\V1\AlertRuleController::class, 'events']);
        });

        // Financial Forecasting
        Route::prefix('forecast')->group(function () {
            Route::get('/revenue',   [\App\Http\Controllers\Api\V1\ForecastController::class, 'revenue']);
            Route::get('/cash-flow', [\App\Http\Controllers\Api\V1\ForecastController::class, 'cashFlow']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/financial', [\App\Http\Controllers\Api\V1\ReportsController::class, 'financial']);
            Route::get('/inventory', [\App\Http\Controllers\Api\V1\ReportsController::class, 'inventory']);
            Route::get('/hr',        [\App\Http\Controllers\Api\V1\ReportsController::class, 'hr']);
        });

        // System Metrics (auth required)
        Route::get('/metrics', [\App\Http\Controllers\Api\V1\HealthController::class, 'metrics']);

        // Dashboard Widgets
        Route::prefix('dashboard-widgets')->group(function () {
            Route::get('/',                   [\App\Http\Controllers\Api\V1\DashboardWidgetController::class, 'index']);
            Route::post('/',                  [\App\Http\Controllers\Api\V1\DashboardWidgetController::class, 'store']);
            Route::put('/{dashboardWidget}',  [\App\Http\Controllers\Api\V1\DashboardWidgetController::class, 'update']);
            Route::post('/reorder',           [\App\Http\Controllers\Api\V1\DashboardWidgetController::class, 'reorder']);
            Route::delete('/{dashboardWidget}', [\App\Http\Controllers\Api\V1\DashboardWidgetController::class, 'destroy']);
        });

        // User Preferences
        Route::prefix('preferences')->group(function () {
            Route::get('/',    [\App\Http\Controllers\Api\V1\UserPreferenceController::class, 'index']);
            Route::put('/',    [\App\Http\Controllers\Api\V1\UserPreferenceController::class, 'update']);
            Route::delete('/', [\App\Http\Controllers\Api\V1\UserPreferenceController::class, 'reset']);
        });

        // Tenant Feature Flags
        Route::prefix('features')->group(function () {
            Route::get('/',                   [\App\Http\Controllers\Api\V1\TenantFeatureController::class, 'index']);
            Route::post('/toggle',            [\App\Http\Controllers\Api\V1\TenantFeatureController::class, 'toggle']);
            Route::get('/{feature}/check',    [\App\Http\Controllers\Api\V1\TenantFeatureController::class, 'check']);
        });

        // Email Templates
        Route::prefix('email-templates')->group(function () {
            Route::get('/',                        [\App\Http\Controllers\Api\V1\EmailTemplateController::class, 'index']);
            Route::post('/',                       [\App\Http\Controllers\Api\V1\EmailTemplateController::class, 'store']);
            Route::get('/{emailTemplate}',         [\App\Http\Controllers\Api\V1\EmailTemplateController::class, 'show']);
            Route::put('/{emailTemplate}',         [\App\Http\Controllers\Api\V1\EmailTemplateController::class, 'update']);
            Route::delete('/{emailTemplate}',      [\App\Http\Controllers\Api\V1\EmailTemplateController::class, 'destroy']);
            Route::post('/{emailTemplate}/preview',[\App\Http\Controllers\Api\V1\EmailTemplateController::class, 'preview']);
        });

        // Report Schedules
        Route::prefix('report-schedules')->group(function () {
            Route::get('/',             [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'index']);
            Route::post('/',            [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'store']);
            Route::get('/{reportSchedule}',        [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'show']);
            Route::put('/{reportSchedule}',        [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'update']);
            Route::delete('/{reportSchedule}',     [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'destroy']);
            Route::post('/{reportSchedule}/send',  [\App\Http\Controllers\Api\V1\ReportScheduleController::class, 'sendNow']);
        });

        // Customer Credit Limits
        Route::get('/credit-alerts', [\App\Http\Controllers\Api\V1\CreditLimitController::class, 'alerts']);
        Route::get('/contacts/{contact}/credit',        [\App\Http\Controllers\Api\V1\CreditLimitController::class, 'show']);
        Route::put('/contacts/{contact}/credit',        [\App\Http\Controllers\Api\V1\CreditLimitController::class, 'update']);
        Route::post('/contacts/{contact}/credit/check', [\App\Http\Controllers\Api\V1\CreditLimitController::class, 'check']);

        // Product Variants & Attributes
        Route::prefix('product-attributes')->group(function () {
            Route::get('/',                          [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'indexAttributes']);
            Route::post('/',                         [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'storeAttribute']);
            Route::put('/{productAttribute}',        [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'updateAttribute']);
            Route::delete('/{productAttribute}',     [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'destroyAttribute']);
        });
        Route::prefix('products/{product}/variants')->group(function () {
            Route::get('/',            [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'index']);
            Route::post('/',           [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'store']);
            Route::put('/{variant}',   [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'update']);
            Route::delete('/{variant}',[\App\Http\Controllers\Api\V1\ProductVariantController::class, 'destroy']);
        });
        Route::get('products/{product}/matrix', [\App\Http\Controllers\Api\V1\ProductVariantController::class, 'matrix']);

        // CRM Pipeline Analytics
        Route::prefix('crm/pipeline')->group(function () {
            Route::get('/funnel',      [\App\Http\Controllers\Api\V1\CrmPipelineController::class, 'funnel']);
            Route::get('/win-rate',    [\App\Http\Controllers\Api\V1\CrmPipelineController::class, 'winRate']);
            Route::get('/velocity',    [\App\Http\Controllers\Api\V1\CrmPipelineController::class, 'velocity']);
            Route::get('/leaderboard', [\App\Http\Controllers\Api\V1\CrmPipelineController::class, 'leaderboard']);
        });

        // Leave Balance Management
        Route::prefix('leave')->group(function () {
            Route::get('/types',                        [\App\Http\Controllers\Api\V1\LeaveBalanceController::class, 'types']);
            Route::get('/employees/{employee}/balance', [\App\Http\Controllers\Api\V1\LeaveBalanceController::class, 'employee']);
            Route::post('/allocate',                    [\App\Http\Controllers\Api\V1\LeaveBalanceController::class, 'allocate']);
            Route::get('/team',                         [\App\Http\Controllers\Api\V1\LeaveBalanceController::class, 'team']);
        });

        // Inventory Reorder Suggestions
        Route::prefix('reorder')->group(function () {
            Route::get('/suggestions', [\App\Http\Controllers\Api\V1\ReorderController::class, 'suggestions']);
            Route::get('/summary',     [\App\Http\Controllers\Api\V1\ReorderController::class, 'summary']);
        });

        // API Token Management
        Route::prefix('tokens')->group(function () {
            Route::get('/',           [\App\Http\Controllers\Api\V1\ApiTokenController::class, 'index']);
            Route::post('/',          [\App\Http\Controllers\Api\V1\ApiTokenController::class, 'store']);
            Route::delete('/all',     [\App\Http\Controllers\Api\V1\ApiTokenController::class, 'destroyAll']);
            Route::delete('/{tokenId}', [\App\Http\Controllers\Api\V1\ApiTokenController::class, 'destroy']);
        });

        // Webhook Management
        Route::get('/webhooks/events', [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'events']);
        Route::prefix('webhooks')->group(function () {
            Route::get('/',                         [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'index']);
            Route::post('/',                        [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'store']);
            Route::get('/{webhook}',                [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'show']);
            Route::put('/{webhook}',                [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'update']);
            Route::delete('/{webhook}',             [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'destroy']);
            Route::get('/{webhook}/deliveries',     [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'deliveries']);
            Route::post('/{webhook}/ping',          [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'ping']);
            Route::post('/{webhook}/rotate-secret', [\App\Http\Controllers\Api\V1\WebhookApiController::class, 'rotateSecret']);
        });
    });
});
