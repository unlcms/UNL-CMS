/**
 * @file
 * README file for Workbench Media.
 */

Workbench Media
Integration of Media module with the Workbench module.

CONTENTS
--------

1.  Introduction
1.1   Use-case
1.2   Examples
2.  Installation
3.  Permissions
4.  Configuration
5.  Using the module
6.  Troubleshooting
7.  Developer notes
7.1   API documentation
7.2   Database schema
8.  Feature roadmap


----
1.  Introduction

Workbench Media provides the integration of the Media module with Workbench
and its suite of modules.

----
1.1  Use-case

Media module is a solution for managing various media files including
images, video, and audio.  The Media Workbench module allows a user to add
media items without having to leave the Workbench.


----
1.2  Examples

To support large numbers of photo galleries or slideshows it's nice to provide
a reverse lookup.  Create your photo gallery, then create media items and
use a node reference field to choose which gallery will display the media item.

Adding a media item on its own requires having access to Find Content -> Media
tab.  We wanted to simplify where content administrators need to go to add
any content.

----
2.  Installation

Media is required in order to install Workbench Media.

Install the module and enable it according to Drupal standards.

----
3.  Permissions

Permissions need to be set for Media and Workbench Media modules in order to
make use of Workbench Media.

Workbench Media has one permission:

 -- Use the media add form
 Allows the user to add media directly from the Create Content tab.

A typical permission setup so that a user can take advangate of Workbench Media
looks like:

  Media Permissions
   -- View media
   -- Edit media

  Workbench
   -- Access My Workbench

  Workbench Media
   -- Use the media add form

----
4.  Configuration

There is no specific configuration for Workbench Media.

----
5.  Using the module

When viewing the Workbench, you can access media items in two ways.

Click the Create Content tab.  The Create Media list is at the top.  Click
Upload Media.  From there you select a file to upload.  Click Save, and it is
saved as a media item.

Media items are entities.  You can easily expand a media item to contain any
important meta data you want to store with the uploaded file.  Learn more at
http://drupal.org/project/media

Back in My Workbench, you can see media you have uploaded in the File List tab.

----
6.  Troubleshooting

When I add media, I get a Permission Denied error when I click save.
  -- Fix this by setting the "View media" and "Edit media" permissions for your
  user role.


----
7.  Developer notes

The following section documents technical details of interest to developers.

----
7.1   API documentation

Workbench Media does not offer an API.

----
7.2   Database schema

Workbench Media does not create any tables during installation.

----
8.  Feature roadmap

We think some of the changes made for Workbench Media may roll back into Media.
It could be that Workbench Media and Workbench Files become just one module in
the future.  For now, we felt it was important to provide multiple ways to
support file management. Expect more changes to this module in the future.