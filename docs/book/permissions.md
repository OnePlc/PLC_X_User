# user permissions

Every single request in onePlace is permission based. Only authentication related
functions like login, logout, password reset workflow are whitelisted. 

For every other request, there needs to be a valid permisson for the logged in user
to complete, otherwise the user gets redirected to the login Page.

## Structure

Permissions have a simple structure. They consist of a key and the corresponding
controller. So here for example the basic structure for "add" permission for the "skeleton" module

> add - OnePlace\Skeleton\Controller\SkeletonController

This way you can add all the permissions you like to the `permissions` table in oneplace database.
You can even add "virtual permissions" - so the controller you are referencing to does not have to
exist in the actual codebase. This way you can add custom permissions. We use this e.G in the Tag
Module to have different "add" permissions for every Tag Type
> OnePlace\Tag\Controller\CategoryController
> OnePlace\Tag\Controller\StateController

These permissions we use for "Category" and "State" tags for example. The actual Controller
that performs both actions is "OnePlace\Tag\Controller\TagController" - so we just use these
permissions to have a finer grained permission system.

## Permission Cache

For perfomance reasons, the users permissions are cached during the login process.

**So for permission changes to take effect, you need to logout and login again**