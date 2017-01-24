<?php
// Home
$app->get('/', 'HomeController:indexAction')->setName('home');

// Guest users routes
$app->group('/auth', function () {
    // Sign In
    $this->get('/signin', 'AuthController:getSignInAction')->setName('auth.signin');
    $this->post('/signin', 'AuthController:postSignInAction');
})->add(new App\Middlewares\GuestMiddleware(
    $container->auth,
    $container->router
));

// Authenticated users routes
$app->group('', function () {
    // Auth SingOut and Changes
    $this->group('/auth', function () {
        // Sign Out
        $this->get('/signout', 'AuthController:getSignOutAction')->setName('auth.signout');
        // Change Admin Password
        $this->get('/change/password', 'PasswordController:getChangePasswordAction')->setName('auth.change.password');
        $this->post('/change/password', 'PasswordController:postChangePasswordAction');
        // Edit Admin
        $this->get('/edit', 'AdminController:editAction')->setName('auth.settings');
        // Modify Admin
        $this->post('/modify', 'AdminController:modifyAction');
    });

    // DashBoard
    $this->get('/dashboard', 'DashboardController:indexAction')->setName('dashboard');

    // Project Group
    $this->group('/project', function () {
        // All Projects
        $this->get('/all', 'ProjectController:inProgressAllUsersAction')->setName('project.all');
        // In Progress Projects
        $this->get('/inprogress', 'ProjectController:inProgressAction')->setName('project.inprogress');
        // In Progress Calendar Projects
        $this->get('/inprogress/calendar', 'ProjectController:inProgressCalendarAction')->setName('project.inprogress.calendar');
        // In Progress Calendar Projects
        $this->get('/inprogress/calendar/json', 'ProjectController:inProgressCalendarJsonAction')->setName('project.inprogress.calendar.json');
        // Completed Projects
        $this->get('/completed', 'ProjectController:completedAction')->setName('project.completed');
        // Billed Projects
        $this->get('/billed[/{page:[0-9]+}]', 'ProjectController:billedAction')->setName('project.billed');
        // User Projects
        $this->get('/user/{id:[0-9]+}', 'ProjectController:userAction')->setName('project.user');
        // Tag Projects
        $this->get('/tag/{id:[0-9]+}', 'ProjectController:tagAction')->setName('project.tag');
        // Search Projects
        $this->get('/search', 'ProjectController:searchAction')->setName('project.search');
        // Print Project
        $this->get('/print/{id:[0-9]+}', 'ProjectController:printAction')->setName('project.print');
        // New Project
        $this->get('/new', 'ProjectController:newAction')->setName('project.new');
        // Show Project
        $this->get('/show/{id:[0-9]+}', 'ProjectController:showAction')->setName('project.show');
        // Edit Project
        $this->get('/edit/{id:[0-9]+}', 'ProjectController:editAction')->setName('project.edit');
        // Complete Project
        $this->get('/complete/{id:[0-9]+}', 'ProjectController:completeAction')->setName('project.complete');
        // Reopen Project
        $this->get('/reopen/{id:[0-9]+}', 'ProjectController:reopenAction')->setName('project.reopen');
        // Save Project
        $this->post('/save', 'ProjectController:saveAction')->setName('project.save');
        // Modify Project
        $this->post('/modify/{id:[0-9]+}', 'ProjectController:modifyAction')->setName('project.modify');
        // Delete Project
        $this->get('/delete/{id:[0-9]+}', 'ProjectController:deleteAction')->setName('project.delete');
    });

    // Task Group
    $this->group('/task', function () {
        // User Tasks
        $this->get('/all', 'TaskController:inProgressAction')->setName('task.inprogress');
        // In Progress Calendar Tasks
        $this->get('/inprogress/calendar', 'TaskController:inProgressCalendarAction')->setName('task.inprogress.calendar');
        // In Progress Calendar Tasks
        $this->get('/inprogress/calendar/json', 'TaskController:inProgressCalendarJsonAction')->setName('task.inprogress.calendar.json');
        // Completed User Tasks
        $this->get('/completed', 'TaskController:completedUserAction')->setName('task.completed');
        // Project Tasks
        $this->get('/project/{id:[0-9]+}', 'TaskController:projectAction')->setName('task.project');
        // Completed Project Tasks
        $this->get('/completed/project/{id:[0-9]+}', 'TaskController:completedProjectAction')->setName('task.completed.project');
        // Unassigned Tasks
        $this->get('/unnassigned', 'TaskController:unnassignedUserAction')->setName('task.unnassigned');
        // Assigned Tasks
        $this->get('/assigned', 'TaskController:assignedUserAction')->setName('task.assigned');
        // Independent Tasks
        $this->get('/independent', 'TaskController:independentAction')->setName('task.independent');
        // Independent Tasks
        $this->get('/independent/completed', 'TaskController:independentCompletedAction')->setName('task.independent.completed');
        // New Task
        $this->get('/new[/project/{id:[0-9]+}]', 'TaskController:newAction')->setName('task.new');
        // Show Task
        $this->get('/show/{id:[0-9]+}', 'TaskController:showAction')->setName('task.show');
        // Edit Task
        $this->get('/edit/{id:[0-9]+}', 'TaskController:editAction')->setName('task.edit');
        // Complete Task
        $this->get('/complete/{id:[0-9]+}', 'TaskController:completeAction')->setName('task.complete');
        // Reopne Task
        $this->get('/reopen/{id:[0-9]+}', 'TaskController:reopenAction')->setName('task.reopen');
        // Save Task
        $this->post('/save', 'TaskController:saveAction')->setName('task.save');
        // Modify Task
        $this->post('/modify/{id:[0-9]+}', 'TaskController:modifyAction')->setName('task.modify');
        // Delete Task
        $this->get('/delete/{id:[0-9]+}', 'TaskController:deleteAction')->setName('task.delete');
    });

    // TimeTrack Group
    $this->group('/timetrack', function () {
        // New / Start TimeTrack
        $this->get('/start/task/{id:[0-9]+}', 'TimeTrackController:startAction')->setName('timetrack.start');
        // Stop TimeTrack
        $this->get('/stop', 'TimeTrackController:stopAction')->setName('timetrack.stop');
        // Modify TimeTrack
        $this->post('/modify/{id:[0-9]+}', 'TimeTrackController:modifyAction')->setName('timetrack.modify');
        // Delete TimeTrack
        $this->get('/delete/{id:[0-9]+}', 'TimeTrackController:deleteAction')->setName('timetrack.delete');
    });

    // Expense Group
    $this->group('/expense', function () {
        // New Expense
        $this->get('/new/project/{id:[0-9]+}', 'ExpenseController:newAction')->setName('expense.new');
        // Save Task
        $this->post('/save', 'ExpenseController:saveAction')->setName('expense.save');
        // Edit Expense
        $this->post('/edit/{id:[0-9]+}', 'ExpenseController:editAction')->setName('expense.edit');
        // Modify Expense
        $this->post('/modify/{id:[0-9]+}', 'ExpenseController:modifyAction')->setName('expense.modify');
        // Delete Expense
        $this->get('/delete/{id:[0-9]+}', 'ExpenseController:deleteAction')->setName('expense.delete');
    });

    // Comments Group
    $this->group('/comment', function () {
        // Save Task Comment
        $this->post('/save/task/{id:[0-9]+}', 'CommentController:saveTaskAction')->setName('comment.task.save');
        // Save Project Comment
        $this->post('/save/project/{id:[0-9]+}', 'CommentController:saveProjectAction')->setName('comment.project.save');
    });

    // Notification Group
    $this->group('/notification', function () {
        // All notifications
        $this->get('/all', 'NotificationController:allAction')->setName('notification.all');
        // Show Notification
        $this->get('/show/{id:[0-9]+}', 'NotificationController:showAction')->setName('notification.show');
    });

    // Clients Group
    $this->group('/client', function () {
        // All Clients
        $this->get('/all', 'ClientController:allAction')->setName('client.all');
        // New Client
        $this->get('/new', 'ClientController:newAction')->setName('client.new');
        // Edit Client
        $this->get('/edit/{id:[0-9]+}', 'ClientController:editAction')->setName('client.edit');
        // Save Client
        $this->post('/save', 'ClientController:saveAction')->setName('client.save');
        // Modify Client
        $this->post('/modify/{id:[0-9]+}', 'ClientController:modifyAction')->setName('client.modify');
    });

    // Users Group
    $this->group('/user', function () {
        // All Users
        $this->get('/all', 'UserController:allAction')->setName('user.all');
        // New User
        $this->get('/new', 'UserController:newAction')->setName('user.new');
        // Edit User
        $this->get('/edit/{id:[0-9]+}', 'UserController:editAction')->setName('user.edit');
        // Save User
        $this->post('/save', 'UserController:saveAction')->setName('user.save');
        // Modify User
        $this->post('/modify/{id:[0-9]+}', 'UserController:modifyAction')->setName('user.modify');
    });
})->add(new App\Middlewares\AuthenticatedMiddleware(
    $container->auth,
    $container->flash,
    $container->router
));
