# user form tabs

All form tabs are user based. So within User Manager, you can what tabs
each and every user can see. Have the most fine grained control over your data
you could ever imagine!

Why User based form tabs? Just custom statistics or more detailed information 
just for a certain group of users.

## add new tab

In case you need a new tab for one of your forms to group your fields,
just add it to the database

give it a unique name and attach it to the corresponding form 

```sql
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) 
VALUES ('skeletonrequest-matching', 'skeletonrequest-single', 'Skeletonrequest', 'Matching', 'fas fa-list', '', '1', '\r\n', ''); 
```


## Manage tabs

All tabs can be managed in User Manager. Just select a user, edit and set the tabs you want.
New tabs will automatically be added to User Manager.
