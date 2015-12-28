<?php

/*
 * This file is part of the frenzy-framework package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Cache\Exception;


/*
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class InvalidConfigException extends BaseException
{
    public static function create($name, $type)
    {
        return new self(
            'Invalid cache config "'.$name.'". It must be '.$type
        );
    }
}