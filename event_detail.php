<!-- Thêm đoạn này vào phần hiển thị event detail, nơi có nút đăng ký -->

<?php
// Check if user already registered
$isRegistered = false;
if (isset($_SESSION['uid'])) {
    $checkReg = "SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'";
    $stmtCheck = mysqli_prepare($conn, $checkReg);
    mysqli_stmt_bind_param($stmtCheck, "is", $event_id, $_SESSION['uid']);
    mysqli_stmt_execute($stmtCheck);
    $isRegistered = mysqli_num_rows(mysqli_stmt_get_result($stmtCheck)) > 0;
    mysqli_stmt_close($stmtCheck);
}

$spots_left = $event['max_volunteers'] - $event['registered_count'];
$is_full = $spots_left <= 0;
?>

<!-- Registration Button -->
<div class="registration-section mt-4">
    <?php if (!isset($_SESSION['uid'])): ?>
        <a href="login.php?redirect=event_detail&event_id=<?php echo $event['id']; ?>" class="btn btn-primary btn-lg w-100">
            <i class="fas fa-sign-in-alt"></i> Login to Register
        </a>
    <?php elseif ($isRegistered): ?>
        <button class="btn btn-success btn-lg w-100" disabled>
            <i class="fas fa-check-circle"></i> Already Registered
        </button>
    <?php elseif ($is_full): ?>
        <button class="btn btn-danger btn-lg w-100" disabled>
            <i class="fas fa-times-circle"></i> Event Full - No Spots Available
        </button>
    <?php else: ?>
        <button class="btn btn-primary btn-lg w-100" onclick="showRegistrationConfirm(<?php echo $event['id']; ?>, '<?php echo addslashes($event['event_title']); ?>', <?php echo $spots_left; ?>)">
            <i class="fas fa-hand-paper"></i> Register for This Event
        </button>
        <div class="text-center mt-2">
            <small class="text-info">
                <i class="fas fa-users"></i> <?php echo $spots_left; ?> spots remaining
            </small>
        </div>
    <?php endif; ?>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmRegistrationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-hand-paper"></i> Confirm Registration
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <h4 id="modalEventTitle" class="mb-3"></h4>
                <p class="text-muted">Do you want to register for this event?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <span id="modalSpotsInfo"></span>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmRegisterBtn">
                    <i class="fas fa-check"></i> OK, Register Me
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedEventId = null;

function showRegistrationConfirm(eventId, eventTitle, spotsLeft) {
    selectedEventId = eventId;
    document.getElementById('modalEventTitle').textContent = eventTitle;
    document.getElementById('modalSpotsInfo').textContent = spotsLeft + ' spots remaining';
    
    const modal = new bootstrap.Modal(document.getElementById('confirmRegistrationModal'));
    modal.show();
}

// When user confirms, redirect to registration form
document.getElementById('confirmRegisterBtn').addEventListener('click', function() {
    if (selectedEventId) {
        window.location.href = 'register_events.php?id=' + selectedEventId;
    }
});
</script>

<style>
.registration-section {
    position: sticky;
    bottom: 20px;
    z-index: 100;
}

.modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-header {
    border-radius: 15px 15px 0 0;
}

#modalEventTitle {
    color: #333;
    font-weight: 600;
}
</style>