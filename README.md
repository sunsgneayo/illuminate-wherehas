# illuminate-whereHas
Wherehas of illuminate/database executes SQL statements, the response time is not ideal



`illuminate/database` model的whereHas执行的SQL如下
```sql
 select * from `group_member` where exists 
     (select * from `group` 
               where `group_member`.`group_id` = `group`.`group_id` 
                 and `id` <> 1 
                 and `deleted_at` is null
     ) and `deleted_at` is null
```
在业务测试中，上述SQL对耗时：

`sunsgne/where-has` model的whereHas执行的SQL如下
```sql
 select * from `group_member` where in 
     (select * from `group` 
               where `group_member`.`group_id` = `group`.`group_id` 
                 and `id` <> 1 
                 and `deleted_at` is null
     ) and `deleted_at` is null
```
在业务测试中，上述SQL对耗时：
