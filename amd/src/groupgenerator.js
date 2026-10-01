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
 * groupgenerator.js
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function ($) {
    function alphaLabel(index) {
        var label = "";
        var number = index + 1;
        while (number > 0) {
            number--;
            label = String.fromCharCode(65 + (number % 26)) + label;
            number = Math.floor(number / 26);
        }
        return label;
    }

    function escapeHtml(value) {
        return $("<div>").text(value).html();
    }

    function render(config) {
        var count = parseInt($(config.count).val(), 10) || 0;
        var capacity = parseInt($(config.capacity).val(), 10) || 0;
        if (count < 1 || capacity < 1) {
            $(config.target).empty();
            return;
        }
        count = Math.min(count, 200);
        var html = '<div class="card"><div class="card-body">';
        html += '<h5 class="card-title">' + escapeHtml(config.title) + '</h5>';
        html += '<div class="d-flex flex-wrap gap-2">';
        for (var i = 0; i < count; i++) {
            var seats = config.seatsSuffix.replace("__COUNT__", capacity);
            html += '<div class="border rounded px-3 py-2">';
            html += '<strong>' + escapeHtml(config.groupPrefix + " " + alphaLabel(i)) + '</strong>';
            html += '<div class="small text-muted">' + escapeHtml(seats) + '</div></div>';
        }
        html += '</div></div></div>';
        $(config.target).html(html);
    }

    return {
        init: function (configs) {
            var config = configs[0] || configs;
            $(config.button).on("click", function (event) {
                event.preventDefault();
                render(config);
            });
        }
    };
});
