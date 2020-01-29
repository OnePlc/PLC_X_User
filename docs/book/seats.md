# user seats

onePlace ships with unlimited user accounts by default.

If you want to add a user-based licencing model to your app, you can
add "seats" to oneplace user, so you can limit the amount of users
that can be created.

we also use this for our hosted solutions and subscription based models.

## installation

Just add setting `user-limit` to the `settings` table. 

Here for example the code for 5 seats

```sql
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES
('user-limit', '5');
```