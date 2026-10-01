# mod_signup - Activity signup

A Moodle activity for allocating students to internal activity groups with capacity control and optional waiting lists.

## How it works

When the activity is created, it can generate a chosen number of groups with an initial capacity. Students select an
available group and, when a group is full, can enter its waiting list if that option is enabled.

When a confirmed participant leaves, the first learner in the waiting list is promoted automatically. Teachers can also
decide whether students may change or cancel their signup.

## Group management

Groups can have custom names, individual capacities and an optional leader. The first confirmed member controls team
settings until a leader is selected; after that, the designated leader controls those settings.

Teachers have a management page for group names, capacity, rename permission, leader permission and leader assignment,
plus a report that can be exported to CSV.

Concurrent signups are protected by Moodle's Lock API to prevent overbooking. The activity also integrates with backup,
restore and Moodle's Privacy API.
