Swift Web Development Framework - v0.0.1
====

This is a formerly private project that I have made public with the hope that it will be of use to others. PLEASE NOTE: The SWDF is NOT ready for production! It is still in a very early stage of development.

The SWDF is a primarily procedural style MVC PHP framework which allows you to quickly and simply create websites. While much of the code is in the procedural style, it is not afraid to use OOP when the situation warrants it. Unlike many other PHP frameworks the SWDF tries to take a balanced approach to OOP, recognizing that sometimes it can be more of a hinderance than a help, while at other times, it can be incredibly useful (such as the SWDF_image_resizer class).

You are free to use this code in any of your projects (although I strongly urge you not to attempt to use it in live production). Patches/Bug Reports are more than welcome.

To use this code: 

`git clone git://github.com/swiftoid/swdf.git`

`git submodule init`

`git submodule update`

Then import default_database.sql into MySQL.

`mysql -u root < default_database.sql`

<small>(The SWDF assumes the default state of mysql where the root user has no password. Hopefully your setup is different though. If so you will need to specify the DB details in `settings/_settings.php` before the SWDF will work.)</small>

PLEASE NOTE: Config files are included in the repo as examples. To avoid a `git pull` overwriting your settings files, I recommend you copy the repo to a separate directory from the cloned repo to use as a testing environment.

## Branching Model

The SWDF uses the branching/development model described [here](http://nvie.com/posts/a-successful-git-branching-model/).

If you wish to test the latest development version, checkout branch `develop`.

## Versioning

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

The current version number can be found at the top of the README.md file and the main index.php file.

For more information please visit [http://semver.org/](http://semver.org/).

## Licence (Simplified BSD License)

For licensing details, see [LICENSE](https://github.com/swiftoid/SWDF/blob/master/LICENSE)