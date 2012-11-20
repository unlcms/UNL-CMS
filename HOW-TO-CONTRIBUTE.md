Development is handled through GitHub

All code changes must be committed via git to a local fork
and contributed back to the project via a pull request.

Ideally each developer should have a fork of the project
on GitHub where they can push changes.

In your local clone:

 * git pull origin develop
 * git checkout -b topics/whatever-you-work-on (or bugfix/NUM — for bugs)
 * write code and commit
 * git push origin topics/whatever-you-work-on
 * go on github.com/github.unl.edu and open a pull request from your branch to develop
 * have someone else review

Another developer will review your changes and merge in to the develop branch.
