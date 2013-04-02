TRAILING SLASH DRUPAL MODULE
----------------------------

What is it?
-----------
Adds trailing slashes to all Drupal generated clean URLs.
For example: example.com/user/.

How do I install it?
--------------------
1. Install and enable this module using one of the following methods:
http://drupal.org/documentation/install/modules-themes/modules-7

2. Add a redirect for your website that enforces trailing slashes using one of the following methods (not having duplicate pages is good for SEO):

Apache mod_rewrite example (in .htaccess):

RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_METHOD} !=post [NC]
RewriteRule ^(.*(?:^|/)[^/\.]+)$ $1/ [L,R=301]

IIS URL Rewrite example (in web.config):

<configuration>
	<system.webServer>
		<rewrite>
			<rules>
				<rule name="Redirect to Trailing Slashes" enabled="true" stopProcessing="true">
 					<match url="^(.*(?:^|/)[^/\.]+)$" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false">
						<add input="{REQUEST_METHOD}" pattern="post" negate="true" />
					</conditions>
					<action type="Redirect" url="{R:1}/" />
				</rule>
			</rules>
		</rewrite>
	</system.webServer>
</configuration>

GlobalRedirect module [http://drupal.org/project/globalredirect]
Installing this module is a good way to perform global redirects if you can't or don't want to use web server configured redirects.

3. Go to Administration > Configuration > Search and metadata > Clean URLs in
Drupal and ensure that Enable trailing slashes is checked. Easy as that!!

Known Issues/Bugs
-----------------
None.

Sponsors
--------
Development of this module was sponsored by the Australian War Memorial [http://www.awm.gov.au/].
