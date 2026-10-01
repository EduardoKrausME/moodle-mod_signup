# mod_signup - Activity signup

A small Moodle activity for allocating students to internal activity groups with capacity control and optional waiting
lists.

## Main features

- Automatically creates a chosen number of groups when the activity is created.
- Sets the same initial capacity for all generated groups.
- Optional waiting list with automatic FIFO promotion when a seat becomes available.
- Students can change or cancel their signup when the teacher allows it.
- Per-group custom names.
- Per-group optional leader selection.
- The first confirmed member controls team settings until a leader is selected; then the leader controls them.
- Teacher management page for group name, capacity, rename permission, leader permission, and leader assignment.
- Teacher report with CSV export.
- Moodle Lock API protection against concurrent overbooking.
- Privacy API and backup/restore support.

## Installation

Copy the `signup` directory to `mod/signup` and visit Site administration > Notifications.

Requires Moodle 4.5 or later.
