<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Get username from URL parameter
$username = $_GET['username'] ?? '';

if (empty($username)) {
    header('Location: /');
    exit();
}

// Get user by username
$profile_user = get_user_by_username($username);
if (!$profile_user) {
    header('Location: /404.php');
    exit();
}

// Check if current user can view this profile
$current_user_id = $_SESSION['user_id'] ?? null;
if (!can_view_profile($current_user_id, $profile_user['id'])) {
    header('Location: /login.php');
    exit();
}

// Get complete profile data for header
$profile_data = get_user_profile_complete($profile_user['id']);
$user_stats = get_user_stats($profile_user['id']);

// Check if current user is following this profile
$is_following = false;
if ($current_user_id && $current_user_id != $profile_user['id']) {
    $is_following = is_following($current_user_id, $profile_user['id']);
}

// Set active tab for navigation
$active_tab = 'events';

// Get user events
$events = get_user_events($profile_user['id'], 20, 0);
$upcoming_events = array_filter($events, function($event) {
    return strtotime($event['start_date']) > time();
});
$past_events = array_filter($events, function($event) {
    return strtotime($event['start_date']) <= time();
});

$page_title = $profile_user['display_name'] ?: $profile_user['username'] . "'s Events";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - IslamWiki</title>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/main.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/user_profile.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/events.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php include '../../includes/profile_template.php'; ?>
    
    <!-- Events Section - Full Width -->
    <div class="events-section">
        <div class="events-header">
            <h2>Events</h2>
            <?php if ($current_user_id == $profile_user['id']): ?>
                <button class="create-event-btn" onclick="openCreateEventModal()">
                    <i class="fas fa-plus"></i> Create Event
                </button>
            <?php endif; ?>
        </div>
        
        <div class="events-content">
            <div class="events-container">
                <!-- Upcoming Events -->
                <?php if (!empty($upcoming_events)): ?>
                <div class="events-group">
                    <h3 class="events-group-title">
                        <i class="fas fa-calendar-alt"></i> Upcoming Events
                        <span class="event-count">(<?php echo count($upcoming_events); ?>)</span>
                    </h3>
                    <div class="events-grid">
                        <?php foreach ($upcoming_events as $event): ?>
                            <div class="event-card" data-event-id="<?php echo $event['id']; ?>">
                                <div class="event-image">
                                    <?php if (!empty($event['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($event['cover_image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                    <?php else: ?>
                                        <div class="default-event-image">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="event-date-badge">
                                        <span class="month"><?php echo date('M', strtotime($event['start_date'])); ?></span>
                                        <span class="day"><?php echo date('j', strtotime($event['start_date'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="event-content">
                                    <h4 class="event-title">
                                        <a href="/event/<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['title']); ?></a>
                                    </h4>
                                    
                                    <div class="event-details">
                                        <div class="event-detail">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo format_event_time($event['start_date'], $event['end_date']); ?></span>
                                        </div>
                                        
                                        <?php if (!empty($event['location'])): ?>
                                        <div class="event-detail">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($event['location']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="event-detail">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo $event['attendees_count']; ?> <?php echo $event['attendees_count'] == 1 ? 'person' : 'people'; ?> going</span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($event['description'])): ?>
                                    <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . (strlen($event['description']) > 100 ? '...' : ''); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="event-actions">
                                        <?php if ($current_user_id): ?>
                                            <?php if (is_event_attendee($current_user_id, $event['id'])): ?>
                                                <button class="btn btn-secondary attending-btn" onclick="toggleAttendance(<?php echo $event['id']; ?>)">
                                                    <i class="fas fa-check"></i> Going
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-primary attend-btn" onclick="toggleAttendance(<?php echo $event['id']; ?>)">
                                                    <i class="fas fa-plus"></i> Attend
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="/login.php" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Attend
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="/event/<?php echo $event['id']; ?>" class="btn btn-outline">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Past Events -->
                <?php if (!empty($past_events)): ?>
                <div class="events-group">
                    <h3 class="events-group-title">
                        <i class="fas fa-history"></i> Past Events
                        <span class="event-count">(<?php echo count($past_events); ?>)</span>
                    </h3>
                    <div class="events-grid">
                        <?php foreach ($past_events as $event): ?>
                            <div class="event-card past-event" data-event-id="<?php echo $event['id']; ?>">
                                <div class="event-image">
                                    <?php if (!empty($event['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($event['cover_image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                    <?php else: ?>
                                        <div class="default-event-image">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="event-date-badge past">
                                        <span class="month"><?php echo date('M', strtotime($event['start_date'])); ?></span>
                                        <span class="day"><?php echo date('j', strtotime($event['start_date'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="event-content">
                                    <h4 class="event-title">
                                        <a href="/event/<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['title']); ?></a>
                                    </h4>
                                    
                                    <div class="event-details">
                                        <div class="event-detail">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo format_event_time($event['start_date'], $event['end_date']); ?></span>
                                        </div>
                                        
                                        <?php if (!empty($event['location'])): ?>
                                        <div class="event-detail">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($event['location']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="event-detail">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo $event['attendees_count']; ?> <?php echo $event['attendees_count'] == 1 ? 'person' : 'people'; ?> attended</span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($event['description'])): ?>
                                    <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . (strlen($event['description']) > 100 ? '...' : ''); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="event-actions">
                                        <a href="/event/<?php echo $event['id']; ?>" class="btn btn-outline">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- No Events Message -->
                <?php if (empty($events)): ?>
                <div class="no-events">
                    <div class="no-events-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>No Events Yet</h3>
                    <p>
                        <?php if ($current_user_id == $profile_user['id']): ?>
                            You haven't created any events yet. Create your first event to get started!
                        <?php else: ?>
                            <?php echo htmlspecialchars($profile_user['display_name'] ?: $profile_user['username']); ?> hasn't created any events yet.
                        <?php endif; ?>
                    </p>
                    <?php if ($current_user_id == $profile_user['id']): ?>
                        <button class="btn btn-primary" onclick="openCreateEventModal()">
                            <i class="fas fa-plus"></i> Create Your First Event
                        </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div id="createEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Event</h3>
                <button class="close-btn" onclick="closeCreateEventModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="createEventForm">
                    <div class="form-group">
                        <label for="event_title">Event Title *</label>
                        <input type="text" id="event_title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_description">Description</label>
                        <textarea id="event_description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_start_date">Start Date *</label>
                            <input type="date" id="event_start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="event_start_time">Start Time</label>
                            <input type="time" id="event_start_time" name="start_time">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_end_date">End Date</label>
                            <input type="date" id="event_end_date" name="end_date">
                        </div>
                        <div class="form-group">
                            <label for="event_end_time">End Time</label>
                            <input type="time" id="event_end_time" name="end_time">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_location">Location</label>
                        <input type="text" id="event_location" name="location" placeholder="e.g., Community Center, Online, etc.">
                    </div>
                    
                    <div class="form-group">
                        <label for="event_privacy">Privacy</label>
                        <select id="event_privacy" name="privacy">
                            <option value="public">Public</option>
                            <option value="community">Community</option>
                            <option value="followers">Followers Only</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateEventModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="/skins/bismillah/assets/js/events.js"></script>
    <script src="/skins/bismillah/assets/js/user_profile.js"></script>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
