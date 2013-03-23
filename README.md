SWDF
====

Swift Web Development Framework

This is a formerly private project that I have made public with the hope that it will be of use  
to others. PLEASE NOTE: The SWDF is NOT ready for production! It is still in a very early stage 
of development.

The SWDF is a primarily procedural style MVC PHP framework which allows you to quickly and simply 
create websites. While much of the code is in the procedural style, it is not afraid to use OOP 
when the situation warrants it. Unlike many other PHP frameworks the SWDF tries to take a 
balanced approach to OOP, recognizing that sometimes it can be more of a hinderance than a help, 
while at other times, it can be incredibly useful (such as the SWDF_image_resizer class).

You are free to use this code in any of your projects (although I don't recommend it at present, 
as it is still unfinished even to a state where it would be usable in production). Patches/Bug 
reports are more than welcome.

To use this code: 

git clone git://github.com/swiftoid/swdf.git

Then import default_database.sql into MySQL.

PLEASE NOTE: Config files are included in the repo as examples. To avoid a "git pull" overwriting 
your settings files, I recommend you copy the repo to a separate directory from the cloned repo 
to use as a testing environment.