<?php

use Illuminate\Support\Facades\Route;

ApiRoute::group(['namespace' => 'Modules\RestAPI\Http\Controllers'], function () {

    ApiRoute::get('app', ['as' => 'api.app', 'uses' => 'AppController@app']);
//    ApiRoute::post('check-company', ['as' => 'api.check-company', 'uses' => 'AppController@checkCompany']);

    // Forgot Password
    ApiRoute::post(
        'auth/forgot-password',
        ['as' => 'api.auth.forgotPassword', 'uses' => 'AuthController@forgotPassword']
    );

    // Auth routes
    ApiRoute::post('auth/login', ['as' => 'api.auth.login', 'uses' => 'AuthController@login']);
    

    // Two-factor authentication routes
    ApiRoute::post('auth/two-factor', ['as' => 'api.twoFactorAuth', 'uses' => 'AuthController@twoFactorAuth', 'middleware' => ['auth:sanctum']]);
    ApiRoute::post('auth/resend-two-factor-code', ['as' => 'api.resendTwoFactorCode', 'uses' => 'AuthController@resendTwoFactorCode', 'middleware' => ['auth:sanctum']]);

    ApiRoute::post('auth/reset-password', ['as' => 'api.auth.resetPassword', 'uses' => 'AuthController@resetPassword']);

    // File view does not require Auth
    ApiRoute::get('/file/{name}', ['as' => 'file.show', 'uses' => 'FileController@download']);

    // We public file uploads, but only for certain types, which we will check in request
    ApiRoute::post('/file', ['as' => 'file.store', 'uses' => 'FileController@upload']);
    ApiRoute::get('/lang', ['as' => 'lang', 'uses' => 'LanguageController@lang', 'middleware' => ['api.auth.optional']]);
});

ApiRoute::group(['namespace' => 'Modules\RestAPI\Http\Controllers', 'middleware' => ['auth:sanctum', 'api.auth']], function () {
    ApiRoute::post('logintoworkspace', ['as' => 'api.auth.companyLogin', 'uses' => 'AuthController@companyLogin']);
    ApiRoute::post('auth/logout', ['as' => 'api.auth.logout', 'uses' => 'AuthController@logout']);
    ApiRoute::get('auth/refresh', ['as' => 'api.auth.refresh', 'uses' => 'AuthController@refresh']);

    ApiRoute::get('dashboard', ['as' => 'api.dashboard', 'uses' => 'DashboardController@dashboard']);
    ApiRoute::get('dashboard/me', ['as' => 'api.dashboard.me', 'uses' => 'DashboardController@myDashboard']);
    ApiRoute::get('dashboard/aniversaries', ['as' => 'api.dashboard.aniversaries', 'uses' => 'DashboardController@aniversaries']);
    ApiRoute::get('auth/me', ['as' => 'api.auth.me', 'uses' => 'AuthController@me']);
    ApiRoute::get('/project/me', ['as' => 'project.me', 'uses' => 'ProjectController@me']);

    ApiRoute::get('/shift-schedule/me', ['as' => 'api.shift.me', 'uses' => 'EmployeeShiftScheduleController@myShiftSchedules']);
    ApiRoute::resource('/shift-schedule', 'EmployeeShiftScheduleController');
    ApiRoute::get('/employee-shift/{date}', 'EmployeeShiftController@employeeShift');
    ApiRoute::resource('/shift', 'EmployeeShiftController');

    ApiRoute::get('company', ['as' => 'api.company', 'uses' => 'CompanyController@company']);

    ApiRoute::post('/project/{project_id}/members', ['as' => 'project.member', 'uses' => 'ProjectController@members']);
    ApiRoute::delete(
        '/project/{project_id}/member/{id}',
        [
            'as' => 'project.member.delete',
            'uses' => 'ProjectController@memberRemove',
        ]
    );
    ApiRoute::resource('project', 'ProjectController');
    ApiRoute::resource('project-category', 'ProjectCategoryController');
    ApiRoute::resource('project-status', 'ProjectStatusController');
    ApiRoute::resource('currency', 'CurrencyController');

    ApiRoute::get('/task/me', ['as' => 'task.me', 'uses' => 'TaskController@me']);
    ApiRoute::get('/task/remind/{id}', ['as' => 'task.remind', 'uses' => 'TaskController@remind']);

    ApiRoute::resource('/task/{task_id}/subtask', 'SubTaskController');
    ApiRoute::get('/task/{task_id}/history', ['as' => 'api.task.history', 'uses' => 'TaskHistoryController@taskHistory']);
    ApiRoute::resource('/task/{task_id}/time-log', 'TimeLogController');
    ApiRoute::resource('/task/{task_id}/comment', 'TaskCommentController', ['only' => ['store', 'update', 'destroy']]);
    ApiRoute::resource('/task/{task_id}/note', 'TaskNoteController', ['only' => ['store', 'update', 'destroy']]);
    ApiRoute::resource('/task/{task_id}/file', 'TaskFileController', ['only' => ['index', 'store', 'update', 'destroy']]);
    ApiRoute::resource('/task/{task_id}/timelog', 'TimeLogController', ['only' => ['index']]);
    ApiRoute::resource('task', 'TaskController');
    ApiRoute::resource('task-category', 'TaskCategoryController');
    ApiRoute::resource('taskboard-columns', 'TaskboardColumnController');
//    ApiRoute::resource('time-log', 'TimeLogController');

    ApiRoute::get('/lead/me', ['as' => 'lead.me', 'uses' => 'LeadController@me']);
    ApiRoute::resource('lead', 'LeadController');
    ApiRoute::resource('lead-category', 'LeadCategoryController');
    ApiRoute::resource('lead-source', 'LeadSourceController');
    ApiRoute::resource('lead-agent', 'LeadAgentController');
    ApiRoute::resource('lead-status', 'LeadStatusController');
    ApiRoute::resource('client', 'ClientController');
    ApiRoute::resource('client-category', 'ClientCategoryController');
    ApiRoute::resource('client-sub-category', 'ClientSubCategoryController');
    ApiRoute::resource('department', 'DepartmentController');
    ApiRoute::resource('designation', 'DesignationController');

    ApiRoute::resource('holiday', 'HolidayController');

    ApiRoute::resource('contract-type', 'ContractTypeController');
    ApiRoute::resource('contract', 'ContractController');

    ApiRoute::resource('notice', 'NoticeController');
    ApiRoute::resource('event', 'EventController');
    ApiRoute::get('/dashboard/calendar', ['as' => 'api.calendar.me', 'uses' => 'EventController@me']);

    // ApiRoute::resource('appreciations', 'AppreciationController');
    ApiRoute::get('/dashboard/appreciations', ['as' => 'api.appreciations.me', 'uses' => 'AppreciationController@dashboard']);

    ApiRoute::get('/estimate/send/{id}', ['as' => 'estimate.send', 'uses' => 'EstimateController@sendEstimate']);
    ApiRoute::resource('estimate', 'EstimateController');

    ApiRoute::get('/invoice/send/{id}', ['as' => 'invoice.send', 'uses' => 'InvoiceController@sendInvoice']);
    ApiRoute::get(
        '/invoice/payment-reminder/{id}',
        ['as' => 'invoice.payment-reminder', 'uses' => 'InvoiceController@remindForPayment']
    );
    ApiRoute::resource('invoice', 'InvoiceController');

    ApiRoute::get(
        'userchat/message-setting',
        ['as' => 'api.message-setting', 'uses' => 'UserChatController@messageSetting']
    );

    ApiRoute::get(
        'userchat/user-list',
        ['as' => 'api.user-list', 'uses' => 'UserChatController@userList']
    );

    ApiRoute::get(
        'userchat/messages/{userid}',
        ['as' => 'api.messages.id', 'uses' => 'UserChatController@getMessages']
    );

    ApiRoute::resource('userchat', 'UserChatController');

    ApiRoute::get('timelog/me', ['as' => 'api.timelog.me', 'uses' => 'TimeLogController@me']);
    ApiRoute::resource('timelog', 'TimeLogController', ['only' => ['index', 'store', 'update']]);

    ApiRoute::get('/ticket/me', ['as' => 'ticket.me', 'uses' => 'TicketController@me']);
    ApiRoute::resource('ticket', 'TicketController');
    ApiRoute::post(
        'ticket-reply-file',
        ['as' => 'api.ticket-reply-file', 'uses' => 'TicketReplyController@ticketReplyFile']
    );
    ApiRoute::resource('ticket-reply', 'TicketReplyController');
    ApiRoute::resource('ticket-group', 'TicketGroupController', ['only' => ['index']]);
    ApiRoute::resource('ticket-channel', 'TicketChannelController', ['only' => ['index']]);
    ApiRoute::resource('ticket-type', 'TicketTypeController', ['only' => ['index']]);

    ApiRoute::resource('product', 'ProductController');
    ApiRoute::get(
        '/employee/last-employee-id',
        [
            'as' => 'employee.last-employee-id',
            'uses' => 'EmployeeController@lastEmployeeID',
        ]
    );
    ApiRoute::get('/employee/me', ['as' => 'employee.me', 'uses' => 'EmployeeController@me']);
    ApiRoute::resource('employee', 'EmployeeController');

    ApiRoute::resource('user', 'UserController', ['only' => ['index']]);

    ApiRoute::resource('expense', 'ExpenseController');

    // Notifications
    ApiRoute::get('notifications', ['as' => 'api.notifications.index', 'uses' => 'NotificationController@index']);
    ApiRoute::post('notifications/{id}/read', ['as' => 'api.notifications.read', 'uses' => 'NotificationController@markAsRead']);
    ApiRoute::post('notifications/read-all', ['as' => 'api.notifications.readAll', 'uses' => 'NotificationController@markAllAsRead']);

    ApiRoute::get('leave/by-date', ['as' => 'api.leave.by-date', 'uses' => 'LeaveController@byDate']);
    ApiRoute::get('leave/by-unique-id', ['as' => 'api.leave.by-unique-id', 'uses' => 'LeaveController@byUniqueId']);
    ApiRoute::post('leave/leave-multiple', ['as' => 'api.leave.leave-multiple-dates-apply', 'uses' => 'LeaveController@multiDatesLeaveApply']);
    ApiRoute::resource('leave', 'LeaveController');
    ApiRoute::get('dashboard/leaves', ['as' => 'api.leaves.dashboard', 'uses' => 'LeaveController@dashboard']);
    ApiRoute::post('leave/apply', ['as' => 'api.leave.apply', 'uses' => 'LeaveController@apply']);
    ApiRoute::resource('leave-type', 'LeaveTypeController');

    ApiRoute::post('/device/register', ['as' => 'device.register', 'uses' => 'DeviceController@register']);
    ApiRoute::post('/device/unregister', ['as' => 'device.unregister', 'uses' => 'DeviceController@unregister']);

    ApiRoute::get('/attendance/setting', ['as' => 'attendance.setting', 'uses' => 'AttendanceController@attendanceSetting']);
    ApiRoute::get('/attendance/today', ['as' => 'attendance.today', 'uses' => 'AttendanceController@today']);
    ApiRoute::get('/attendance/date-wise/{date}', ['as' => 'attendance.date-wise', 'uses' => 'AttendanceController@dateWise']);
    ApiRoute::get('/attendance/current-clock-in', ['as' => 'attendance.CurrentClockIn', 'uses' => 'AttendanceController@CurrentClockIn']);
    ApiRoute::post('/attendance/clock-in', ['as' => 'attendance.clockIn', 'uses' => 'AttendanceController@clockIn']);
    ApiRoute::post(
        '/attendance/clock-out/{attendance}',
        [
            'as' => 'attendance.clockOut',
            'uses' => 'AttendanceController@clockOut',
        ]
    );
    ApiRoute::resource('/attendance', 'AttendanceController');

    ApiRoute::resource('/tax', 'TaxController', ['only' => ['index']]);

    ApiRoute::get('/bell-notifications', ['as' => 'bell-notifications.index', 'uses' => 'BellNotificationController@allNotifications']);
    ApiRoute::get('/all-unread-notifications', ['as' => 'bell-notifications.unread', 'uses' => 'BellNotificationController@unreadNotifications']);
    ApiRoute::get('/bell-notifications/{id}', ['as' => 'bell-notifications.show', 'uses' => 'BellNotificationController@notificationById']);
    ApiRoute::post('/mark-as-read', ['as' => 'bell-notifications.mark-as-read', 'uses' => 'BellNotificationController@markAsRead']);
    ApiRoute::post('/mark-all-as-read', ['as' => 'bell-notifications.mark-all-as-read', 'uses' => 'BellNotificationController@markAllAsRead']);
});

// ─── Desktop Agent API Routes ────────────────────────────────────────────────

Route::prefix('agent')->group(function () {
    Route::post('/login', [\Modules\RestAPI\Http\Controllers\Agent\AgentAuthController::class, 'login'])
        ->name('api.agent.login');
    Route::get('/auth/social-settings', [\Modules\RestAPI\Http\Controllers\Agent\AgentSocialAuthController::class, 'settings'])
        ->name('api.agent.social.settings');
    Route::get('/auth/social/{provider}', [\Modules\RestAPI\Http\Controllers\Agent\AgentSocialAuthController::class, 'redirect'])
        ->name('api.agent.social.redirect');
    Route::get('/auth/social/{provider}/callback', [\Modules\RestAPI\Http\Controllers\Agent\AgentSocialAuthController::class, 'callback'])
        ->name('api.agent.social.callback');
});

Route::prefix('agent')->middleware(['auth:sanctum', 'agent.version'])->group(function () {
    // Auth
    Route::post('/logout', [\Modules\RestAPI\Http\Controllers\Agent\AgentAuthController::class, 'logout'])
        ->name('api.agent.logout');

    // Lifecycle
    Route::get('/config', [\Modules\RestAPI\Http\Controllers\Agent\AgentConfigController::class, 'show'])
        ->name('api.agent.config');
    Route::post('/config', [\Modules\RestAPI\Http\Controllers\Agent\AgentConfigController::class, 'update'])
        ->name('api.agent.config.update');
    Route::post('/heartbeat', [\Modules\RestAPI\Http\Controllers\Agent\AgentHeartbeatController::class, 'store'])
        ->name('api.agent.heartbeat');

    // Data upload
    Route::post('/screenshots', [\Modules\RestAPI\Http\Controllers\Agent\AgentScreenshotController::class, 'store'])
        ->name('api.agent.screenshots');
    Route::post('/activity', [\Modules\RestAPI\Http\Controllers\Agent\AgentActivityController::class, 'store'])
        ->name('api.agent.activity');
    Route::post('/activity-windows', [\Modules\RestAPI\Http\Controllers\Agent\AgentActivityWindowController::class, 'store'])
        ->name('api.agent.activity-windows');
    Route::post('/network', [\Modules\RestAPI\Http\Controllers\Agent\AgentNetworkController::class, 'store'])
        ->name('api.agent.network');
    Route::post('/events', [\Modules\RestAPI\Http\Controllers\Agent\AgentEventController::class, 'store'])
        ->name('api.agent.events');

    // Monitoring control
    Route::post('/pause', [\Modules\RestAPI\Http\Controllers\Agent\AgentPauseController::class, 'pause'])
        ->name('api.agent.pause');
    Route::post('/resume', [\Modules\RestAPI\Http\Controllers\Agent\AgentPauseController::class, 'resume'])
        ->name('api.agent.resume');
    Route::get('/productivity-categories', [\Modules\RestAPI\Http\Controllers\Agent\AgentConfigController::class, 'productivityCategories'])
        ->name('api.agent.productivity-categories');
    Route::post('/productivity-categories', [\Modules\RestAPI\Http\Controllers\Agent\AgentConfigController::class, 'seedProductivityCategories'])
        ->name('api.agent.productivity-categories.seed');

    // Employee self-service
    Route::get('/employee/timeline', [\Modules\RestAPI\Http\Controllers\Agent\EmployeeTimelineController::class, 'index'])
        ->name('api.agent.employee.timeline');
    Route::get('/employee/scores', [\Modules\RestAPI\Http\Controllers\Agent\EmployeeScoreController::class, 'index'])
        ->name('api.agent.employee.scores');
    Route::get('/employee/screenshots', [\Modules\RestAPI\Http\Controllers\Agent\EmployeeScreenshotController::class, 'index'])
        ->name('api.agent.employee.screenshots');

    // Task time tracking (desktop agent Tasks tab)
    Route::get('/task/me', [\Modules\RestAPI\Http\Controllers\Agent\AgentTaskController::class, 'me'])
        ->name('api.agent.task.me');
    Route::post('/timelog', [\Modules\RestAPI\Http\Controllers\Agent\AgentTimeLogController::class, 'store'])
        ->name('api.agent.timelog.store');
});

// Manager Dashboard Routes
Route::prefix('manager')->middleware(['auth:sanctum', 'agent.version'])->group(function () {
    Route::get('/employees', [\Modules\RestAPI\Http\Controllers\Agent\ManagerController::class, 'employees'])
        ->name('api.manager.employees');
    Route::get('/employee/{id}/timeline', [\Modules\RestAPI\Http\Controllers\Agent\ManagerController::class, 'employeeTimeline'])
        ->name('api.manager.employee.timeline');
    Route::get('/employee/{id}/screenshots', [\Modules\RestAPI\Http\Controllers\Agent\ManagerController::class, 'employeeScreenshots'])
        ->name('api.manager.employee.screenshots');
    Route::get('/employee/{id}/scores', [\Modules\RestAPI\Http\Controllers\Agent\ManagerController::class, 'employeeScores'])
        ->name('api.manager.employee.scores');
    Route::get('/reports/team', [\Modules\RestAPI\Http\Controllers\Agent\ManagerController::class, 'teamReport'])
        ->name('api.manager.reports.team');
});

// Admin Config Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'agent.version'])->group(function () {
    Route::get('/config', [\Modules\RestAPI\Http\Controllers\Agent\AdminConfigController::class, 'show'])
        ->name('api.admin.config');
    Route::put('/config', [\Modules\RestAPI\Http\Controllers\Agent\AdminConfigController::class, 'update'])
        ->name('api.admin.config.update');
    Route::get('/productivity-categories', [\Modules\RestAPI\Http\Controllers\Agent\AdminConfigController::class, 'productivityCategories'])
        ->name('api.admin.productivity-categories');
    Route::put('/productivity-categories', [\Modules\RestAPI\Http\Controllers\Agent\AdminConfigController::class, 'updateProductivityCategories'])
        ->name('api.admin.productivity-categories.update');
});
