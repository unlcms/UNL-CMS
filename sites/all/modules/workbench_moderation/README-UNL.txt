[#897] Upgrade Workbench Moderation to 2011-07-15 7.x-1.x-dev version
 * r857
 * This was done to address broken Unpublishing in 7.x-1.0-beta7
 * If future releases don't include the fix (not likely) patches will be needed from http://drupal.org/node/1206694

-----------------------------------

[#911] Upgrade Workbench Moderation to 7.x-1.0-beta8 + trigger support
 * Trigger support not in beta8
 * http://drupal.org/files/issues/trigger_support_for_wb_moderation-1079134-10.patch
 *   from http://drupal.org/node/1079134
 * Don't upgrade WB Moderation without first applying this patch unless the new version supports Triggers