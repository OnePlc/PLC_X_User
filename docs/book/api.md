# user-api

There is a basic API for onePlace User module. 
It is used within onePlace for user-based select fields.

## Functions

### list
For external ue
> /user/api/list/0?authkey=YOURKEY

For internal use (with user session present e.G for select fields)
> /user/api/list/0

You have several options for the list function, which you can set
by adding the corresponding request parameter

* `listmode` (select2/entity) (default: select2) return full entity or only array for select2. default is select2
* `listlabel` (any valid field for user) (default: username) return select2 with alternative label 

### get 

Get entitity model of certain user.
> /user/api/get/[USER_ID]?authkey=YOURKEY

For internal use (with user session present e.G for select fields)
> /user/api/get/[USER_ID]

There are currently no options for the get function