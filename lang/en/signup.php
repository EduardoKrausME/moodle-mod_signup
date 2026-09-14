<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * signup.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['allowchanges'] = 'Allow students to change or cancel their signup';
$string['allowleader'] = 'Members may choose leader';
$string['allowrename'] = 'Members may define name';
$string['alreadyregistered'] = 'You are already registered in this group.';
$string['availability'] = 'Signup period';
$string['cannotchange'] = 'This activity does not allow changing or cancelling a signup.';
$string['capacity'] = 'Capacity';
$string['changebutton'] = 'Change group';
$string['chooseleader'] = 'Choose leader';
$string['completionconfirmed'] = 'Require a confirmed signup';
$string['confirmed'] = 'Confirmed';
$string['currentselection'] = 'Your selection';
$string['customgroupname'] = 'Group name';
$string['date'] = 'Date';
$string['defaultallowleader'] = 'Allow each group to choose a leader';
$string['defaultallowleader_help'] = 'If enabled, the group representative can choose a leader from confirmed members. The teacher can change the leader later.';
$string['defaultallowrename'] = 'Allow each group to define its own name';
$string['defaultallowrename_help'] = 'If enabled, the group representative can replace the generated name. The teacher can enable or disable this separately for each group later.';
$string['defaultcapacity'] = 'Maximum students per group';
$string['defaultcapacity_help'] = 'Initial capacity for every automatically created group.';
$string['eventsignupcreated'] = 'Signup created';
$string['eventsignupleft'] = 'Signup cancelled';
$string['eventsignupviewed'] = 'Signup activity viewed';
$string['exportcsv'] = 'Export CSV';
$string['firstmembercontrols'] = 'Until a leader is selected, the earliest confirmed member can configure the group.';
$string['full'] = 'Full';
$string['generategroups'] = 'Create preview';
$string['group'] = 'Group';
$string['groupcount'] = 'Number of groups';
$string['groupcount_help'] = 'Number of internal signup groups to create when this activity is first saved.';
$string['groupcreation'] = 'Automatic group creation';
$string['groupdefaultname'] = 'Group ';
$string['groupfull'] = 'This group is full and the waiting list is disabled.';
$string['groupnameempty'] = 'The group name cannot be empty.';
$string['groupsalreadycreated'] = 'Groups have already been created. Use Manage groups inside the activity to change names, capacities and leaders.';
$string['groupsgeneratedonsave'] = 'The groups shown in the preview are created when the activity is saved.';
$string['groupssaved'] = 'Groups updated.';
$string['invalidgroup'] = 'Invalid group.';
$string['invalidleader'] = 'The selected leader must be a confirmed member of this group.';
$string['joinwaitlist'] = 'Join waiting list';
$string['leader'] = 'Leader';
$string['leavebutton'] = 'Cancel signup';
$string['locktimeout'] = 'The signup is busy. Please try again.';
$string['managegroups'] = 'Manage groups';
$string['members'] = 'Members';
$string['modulename'] = 'Activity signup';
$string['modulenameplural'] = 'Activity signups';
$string['noleader'] = 'No leader selected';
$string['nomembers'] = 'No members';
$string['pluginadministration'] = 'Activity signup administration';
$string['pluginname'] = 'Activity signup';
$string['previewempty'] = 'Set the number of groups and capacity, then click Create preview.';
$string['previewtitle'] = 'Groups that will be created';
$string['privacy:metadata:signup_groups'] = 'Stores internal group configuration that can include a selected leader.';
$string['privacy:metadata:signup_groups:leaderid'] = 'The selected leader of an internal signup group.';
$string['privacy:metadata:signup_members'] = 'Stores activity signup choices and waiting-list entries.';
$string['privacy:metadata:signup_members:groupid'] = 'The selected signup group.';
$string['privacy:metadata:signup_members:status'] = 'Whether the signup is confirmed or waiting.';
$string['privacy:metadata:signup_members:timecreated'] = 'When the signup was created.';
$string['privacy:metadata:signup_members:userid'] = 'The user who signed up.';
$string['report'] = 'Report';
$string['savegroups'] = 'Save groups';
$string['seats'] = ' seats';
$string['seatsremaining'] = ' seats remaining';
$string['signup:addinstance'] = 'Add a new activity signup';
$string['signup:manage'] = 'Manage signup groups';
$string['signup:signup'] = 'Sign up for a group';
$string['signup:view'] = 'View activity signup';
$string['signup:viewreport'] = 'View signup report';
$string['signupbutton'] = 'Sign up';
$string['signupclosed'] = 'Signups are currently closed.';
$string['signupended'] = 'The signup period has ended.';
$string['signupleft'] = 'Your signup has been cancelled.';
$string['signupnotopen'] = 'Signups have not opened yet.';
$string['signupsaved'] = 'Your signup has been saved.';
$string['status'] = 'Status';
$string['teamsettings'] = 'Group settings';
$string['teamsettingsnotallowed'] = 'You cannot change this group\'s settings.';
$string['teamsettingssaved'] = 'Group settings saved.';
$string['timeclose'] = 'Close signups';
$string['timeopen'] = 'Open signups';
$string['waiting'] = 'Waiting list';
$string['waitingmembers'] = 'Waiting list';
$string['waitlist'] = 'Enable waiting list';
$string['waitlist_help'] = 'When a group is full, students may enter its waiting list. Vacancies automatically promote the oldest waiting entry.';
$string['waitlistsaved'] = 'You were added to the waiting list.';
$string['waitposition'] = 'Waiting position: ';
