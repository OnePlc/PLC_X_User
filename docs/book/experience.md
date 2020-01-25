# user experience

We love games ! And we think that people should have fun when they
are at work - so we added some gameification perks to onePlace ;D 

And because we think this is important - it is part of Core, to encourage
developers building apps on top of oneplace, to also have some sort 
of game design within their app.

Users have Levels, and they can gain experience by activity to raise
level. There are also Achievements, another form of rewarding user activity, coming soon.

You can use the users level to block or unblock certain actions, like
"only level 3 users can add new contacts" and so on, so its also an extension to the
permissions system.

### Default Actions

There are some default xp triggers shipped with oneplace

> +10 for login

> +50 for adding a new user

> +5 for editing a user 

### Add your own triggers and activities

You can add your own triggers and activities for the XP system.

If you want to add new activities that users can gain xp from, add them 
to `user_xp_activity` table

````sql
INSERT INTO `user_xp_activity` (`Activity_ID`, `xp_key`, `label`, `xp_base`) VALUES (NULL, 'custom-action', 'My Custom Action', '10'); 
````

Then add the following line of code into all actions you want it to be triggered.
````php
CoreController::$oSession->oUser->addXP('custom-action');
````

This way you can also trigger already existing activities.

Have fun :D