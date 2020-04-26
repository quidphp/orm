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

// fr
// french language content used by this namespace
class Fr extends Main\Lang\Fr
{
    // config
    public static array $config = [

        // error
        'error'=>[

            // label
            'label'=>[
                33=>'Exception base de données',
                34=>'Exception attrapable de base de données'
            ]
        ],

        // direction
        'direction'=>[
            'asc'=>'Ascendant',
            'desc'=>'Descendant',
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
                        'failure'=>'Ajout non effectué'
                    ]
                ],

                // update
                'update'=>[
                    '*'=>[
                        'tooMany'=>'Erreur: plusieurs lignes modifiés',
                        'exception'=>'[message]',
                        'system'=>'Erreur système'
                    ]
                ],

                // delete
                'delete'=>[
                    '*'=>[
                        'notFound'=>'Erreur: aucune ligne effacée',
                        'tooMany'=>'Erreur: plusieurs lignes effacées',
                        'exception'=>'[message]',
                        'system'=>'Erreur système'
                    ]
                ],

                // truncate
                'truncate'=>[
                    '*'=>[
                        'exception'=>'[message]',
                        'system'=>'Erreur système'
                    ]
                ]
            ],

            // pos
            'pos'=>[

                // insert
                'insert'=>[
                    '*'=>[
                        'success'=>'Ajout effectué'
                    ]
                ],

                // update
                'update'=>[
                    '*'=>[
                        'success'=>'Modification effectuée',
                        'partial'=>'Modification partielle effectuée',
                        'noChange'=>'Aucun changement'
                    ]
                ],

                // delete
                'delete'=>[
                    '*'=>[
                        'success'=>'Suppression effectuée'
                    ]
                ],

                // truncate
                'truncate'=>[
                    '*'=>[
                        'success'=>'La table a été vidée'
                    ]
                ]
            ]
        ]
    ];
}

// init
Fr::__init();
?>