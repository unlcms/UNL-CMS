INTRODUCTION
------------

This module allows you to automatize IP ban by cron using module rules.

You create rule which finds IP in watchlog table entries and then module
inserts IP to table for banned IP. By default, IP inserted into blocked_ips
table (admin/config/people/ip-blocking). After installing IP_ranges
module (https://www.drupal.org/project/ip_ranges) you can ban IP range
(aaa.bbb.ccc.0 - aaa.bbb.ccc.255).

Rules for ban IP consist of:
- Type (watchdog type, like "page not found").
- Message pattern (rules seek in watchdog message as "LIKE %message_pattern%").
Use "|" delimiter for multiple values.
- The threshold number of log entries.
- User type (anonymous, authenticated or any).
- Referrer.
- Type IP (single or range)
   Needs installing IP_ranges module (https://www.drupal.org/project/ip_ranges)
   for range ban.

REQUIREMENTS
------------

* Core module Database logging.

RECOMMENDED MODULES
-------------------

* Ip_ranges (https://www.drupal.org/project/ip_ranges)
* Blocked IPs Expire (https://www.drupal.org/project/blocked_ips_expire)

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
   See: https://drupal.org/documentation/install/modules-themes/modules-7
   for further information.

CONFIGURATION
-------------

* Configure at: [Your Site]/admin/config/people/autoban
  or: Administration > Configuration > People > Autoban

* In order to use this module you need the "Administer the automatic ban"
  permission.

* Analyze watchdog table (/admin/reports/dblog) or use wizard
  (/admin/config/people/autoban/analyze).

* Go to the autoban admin page (/admin/config/people/autoban). Create and
  test rules.

* Cron will be ban IP using autoban rules. Check it at
  IP address blocking (/admin/config/people/ip-blocking)
  and IP range bans (/admin/config/people/ip-ranges) pages.

TROUBLESHOOTING
---------------

* A rule's type and message pattern looks in watchlog table. You need put non
  translated value.

* The module using cron for automatic IP ban. If cron is disabled, you can
  click "Ban IPs" button at Show Ban IP for all rules tab.

* The module prevents own IP ban (as single IP or IP included in the banned
  range). If you have a dynamic IP, there is a risk of its own ban.
  In this case it is better to choose a range type in module rules,
  instead a single type.

* The module does not check search engines bots IP. Use Whitelist of IP_ranges
  module.

MAINTAINERS
-----------

Current maintainers:
 * Sergey Loginov (goodboy) - https://drupal.org/user/222910

Module's page:
 * http://drupal-gbtools.rhcloud.com/autoban
