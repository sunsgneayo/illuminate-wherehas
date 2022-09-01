<?php
declare(strict_types=1);

namespace Sunsgne\WhereHas;

use Illuminate\Database\Eloquent;
use Sunsgne\WhereHas\Builder\WhereHasIn;
use Sunsgne\WhereHas\Builder\WhereHasMorphIn;
use Sunsgne\WhereHas\Builder\WhereHasNotIn;
use Webman\Bootstrap;

/**
 * @purpose 注册及追加
 * @date 2022/7/27
 * @author sunsgne
 */
class Init implements Bootstrap
{
    public static function start($worker)
    {
        /** 初始化加载whereHasIn */
        Eloquent\Builder::macro('whereHasIn', function ($relationName, $callable = null) {
            return (new WhereHasIn($this, $relationName, function ($nextRelation, $builder) use ($callable) {
                if ($nextRelation) {
                    return $builder->whereHasIn($nextRelation, $callable);
                }

                if ($callable) {
                    return $builder->callScope($callable);
                }

                return $builder;
            }))->execute();
        });


        /** 初始化 orWhereHasIn */
        Eloquent\Builder::macro('orWhereHasIn', function ($relationName, $callable = null) {
            return $this->orWhere(function ($query) use ($relationName, $callable) {
                return $query->whereHasIn($relationName, $callable);
            });
        });

        /** 初始化  whereHasNotIn */
        Eloquent\Builder::macro('whereHasNotIn', function ($relationName, $callable = null) {
            return (new WhereHasNotIn($this, $relationName, function ($nextRelation, $builder) use ($callable) {
                if ($nextRelation) {
                    return $builder->whereHasNotIn($nextRelation, $callable);
                }

                if ($callable) {
                    return $builder->callScope($callable);
                }

                return $builder;
            }))->execute();
        });
        /**  */
        Eloquent\Builder::macro('orWhereHasNotIn', function ($relationName, $callable = null) {
            return $this->orWhere(function ($query) use ($relationName, $callable) {
                return $query->whereHasNotIn($relationName, $callable);
            });
        });

        /**  */
        Eloquent\Builder::macro('whereHasMorphIn', WhereHasMorphIn::make());

        /**  */
        Eloquent\Builder::macro('orWhereHasMorphIn', function ($relation, $types, $callback = null) {
            return $this->whereHasMorphIn($relation, $types, $callback, 'or');
        });
    }
}
