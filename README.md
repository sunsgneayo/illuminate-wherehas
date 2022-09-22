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

## mysql 中 in 与 exists 的执行计划与效率

### IN
- 对于 IN 查询来说，会先执行子查询，如上边的 t2 表，然后把查询得到的结果和外表 t1 做笛卡尔积，再通过条件进行筛选（这里的条件就是指 name 是否相等），把每个符合条件的数据都加入到结果集中。执行SQL如下；


```select * from t1 where name in (select name from t2);```
- 伪代码如下

```c
for(x in A){
    for(y in B){
     if(condition is true) {result.add();}
    }
}
 ```

- in是把外表和内表做hash连接，先查询内表，再把内表结果与外表匹配，对外表使用索引（外表效率高，可用大表），而内表多大都需要查询，不可避免，故外表大的使用in，可加快效率。


### EXISTS
 - 对于 exists 来说，是先查询遍历外表 t1 ，然后每次遍历时，再检查在内表是否符合匹配条件，即检查是否存在 name 相等的数据。执行SQL如下；

```select * from t1 where name exists (select 1 from t2);```


- 伪代码

```c
for(x in A){
  if(exists condition is true){result.add();}
} 
```
- exists是对外表做loop循环，每次loop循环再对内表（子查询）进行查询，那么因为对内表的查询使用的索引（内表效率高，故可用大表），而外表有多大都需要遍历，不可避免（尽量用小表），故内表大的使用exists，可加快效率；


### 结论

如果查询的两个表大小相当，那么用in和exists差别不大。如果两个表中一个较小，一个是大表，则子查询表大的用exists，子查询表小的用in。

- in是把外表和内表做hash连接，先查询内表；
- exists是对外表做loop循环，循环后在对内表查询；
- 在外表大的时用in效率更快，内表大用exists更快。







   









