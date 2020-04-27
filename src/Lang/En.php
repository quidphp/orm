<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 * Readme: https://github.com/quidphp/orm/blob/master/README.md
 */

namespace Quid\Orm\Lang;
use Quid\Main;

// en
// english language content used by this namespace
class En extends Main\Lang\En
{
    // config
    protected static array $config = [

        // error
        'error'=>[

            // label
            'label'=>[
                33=>'Database exception',
                34=>'Catchable database exception'
            ]
        ],

        // direction
        'direction'=>[
            'asc'=>'Asc',
            'desc'=>'Desc',
        ],

        // db
        'db'=>[

            // label
            'label'=>[],

            // description
            'description'=>[]
        ],

        // table
        'table'=>[

            // label
            'label'=>[],

            // description
            'description'=>[]
        ],

        // col
        'col'=>[

            // label
            'label'=>[

                // *
                '*'=>[
                    'id'=>'Id'
                ]
            ],

            // description
            'description'=>[]
        ],

        // row
        'row'=>[

            // label
            'label'=>[
                '*'=>'[table] #[primary]'
            ],

            // labelName
            'labelName'=>[
                '*'=>'[table] #[primary] | [name]'
            ],

            // description
            'description'=>[]
        ],

        // validate
        'validate'=>[
            'tables'=>[]
        ],

        // required
        'required'=>[
            'tables'=>[]
        ],

        // unique
        'unique'=>[
            'tables'=>[]
        ],

        // editable
        'editable'=>[
            'tables'=>[]
        ],

        // compare
        'compare'=>[
            'tables'=>[]
        ],

        // com
        'com'=>[

            // neg
            'neg'=>[

                // insert
                'insert'=>[
                    '*'=>[
                        'exception'=>'[message]',
                        'failure'=>'Add failed'
                    ]
                ],

                // update
                'update'=>[
                    '*'=>[
                        'tooMany'=>'Error: many rows updated',
                        'exception'=>'[message]',
                        'system'=>'Error system'
                    ]
                ],

                // delete
                'delete'=>[
                    '*'=>[
                        'notFound'=>'Error: no rows deleted',
                        'tooMany'=>'Error: many rows deleted',
                        'exception'=>'[message]',
                        'system'=>'Error system'
                    ]
                ],

                // truncate
                'truncate'=>[
                    '*'=>[
                        'exception'=>'[message]',
                        'system'=>'Error system'
                    ]
                ]
            ],

            // pos
            'pos'=>[

                // insert
                'insert'=>[
                    '*'=>[
                        'success'=>'Add success'
                    ]
                ],

                // update
                'update'=>[
                    '*'=>[
                        'success'=>'Modify success',
                        'partial'=>'Modify partial success',
                        'noChange'=>'No change'
                    ]
                ],

                // delete
                'delete'=>[
                    '*'=>[
                        'success'=>'Delete success'
                    ]
                ],

                // truncate
                'truncate'=>[
                    '*'=>[
                        'success'=>'Table has been truncated'
                    ]
                ]
            ]
        ]
    ];
}

// init
En::__init();
?>