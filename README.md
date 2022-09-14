# illuminate-whereHas
Wherehas of illuminate/database executes SQL statements, the response time is not ideal

## 示例

主表`group_member`写入`130002`条数据，关联表`group`写入`1002`条数据

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

## mysql 中 in 与 exists 的执行计划

### in 执行流程
- 对于 in 查询来说，会先执行子查询，如上边的 t2 表，然后把查询得到的结果和外表 t1 做笛卡尔积，再通过条件进行筛选（这里的条件就是指 name 是否相等），把每个符合条件的数据都加入到结果集中。

### exists 执行流程
 - 对于 exists 来说，是先查询遍历外表 t1 ，然后每次遍历时，再检查在内表是否符合匹配条件，即检查是否存在 name 相等的数据。


