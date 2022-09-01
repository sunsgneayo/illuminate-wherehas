<?php
declare(strict_types=1);

namespace Sunsgne\WhereHas;
use Illuminate\Database\Eloquent;
use Sunsgne\WhereHas\Builder\WhereHasIn;
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

    }
}
