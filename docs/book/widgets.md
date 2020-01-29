# user widgets

Users have dashboard widgets. you can add your own widgets 
if you like.

## add new widgets

Add your widget to the database, so it can be selected in user manager.
You have to define a 
 * `widget_name` (for internal use/partial name)
 * `Widget Title` Title of your widget (shown in user manager)
 * `permission` The permission needed to see the widget (default is home)
 
```sql
INSERT INTO `core_widget` (`Widget_ID`, `widget_name`, `label`, `permission`) VALUES
(NULL, 'widget_name', 'Widget Title', 'index-Application\\Controller\\IndexController'); 
```

Create a partial in your module in ./view/widget with the name `widget_name`.phtml 

## Manage widgets

All widgets can be managed in User Manager. Just select a user, edit and set the widgets you want.
New widgets will automatically be added to User Manager.
