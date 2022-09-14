# illuminate-whereHas
Wherehas of illuminate/database executes SQL statements, the response time is not ideal

示例：主表`group_member`写入`130002`条数据，关联表`group`写入`1002`条数据

`illuminate/database` model的whereHas执行的SQL如下
```sql
 select * from `group_member` where exists 
     (select * from `group` 
               where `group_member`.`group_id` = `group`.`group_id` 
                 and `id` <> 1 
                 and `deleted_at` is null
     ) and `deleted_at` is null
```
在业务测试中，上述SQL对耗时： 0.50499701499939 秒

`sunsgne/illuminate-wherehas` model的whereHas执行的SQL如下
```sql
 select * from `group_member` where in 
     (select * from `group` 
               where `group_member`.`group_id` = `group`.`group_id` 
                 and `id` <> 1 
                 and `deleted_at` is null
     ) and `deleted_at` is null
```
在业务测试中，上述SQL对耗时：0.027166843414307 秒
