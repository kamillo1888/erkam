<?php
/**
 * Created by PhpStorm.
 * Date: 01.09.2016
 *
 * @author Kamil Bednarek <kb@protonmail.ch>
 */
namespace AppBundle\Controller;

use AppBundle\Document\ErrorFixes;
use AppBundle\Query\MongoDBQuery;
use MongoDB\BSON\ObjectID;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorController extends Controller
{
    private function getDataProvider()
    {
        return $this->get('mongodb_provider')->getCollection('error');
    }

    /**
     * @Route("/deployments", name="error_deployments")
     * @Method("GET")
     */
    public function deploymentListAction(Request $request)
    {
        $query = new MongoDBQuery('error_fixes');
        $query->setQuery(['deploy' => true]);
        $query->setSort(['date' => 1]);

        $paginator = $this->get('knp_paginator');
        $fixes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            25
        );

        return $this->render(':error:deployment.html.twig', [
            'deploys' => $fixes
        ]);
    }

    /**
     * @Route("/stats/fixes", name="error_stats_fixes")
     * @Method("GET")
     */
    public function statsFixesAction()
    {
        $collection = $this->get('mongodb_provider')->getCollection('error_fixes');

        // 24 hours stats
        $createdWithin24hours = $collection->aggregate([
            [
                '$group' => [
                    '_id' => [
                        'month' => [
                            '$month' => '$date'
                        ],
                        'day' => [
                            '$dayOfMonth' => '$date'
                        ],
                        'hour' => [
                            '$hour' => '$date'
                        ]
                    ],
                    'count' => [
                        '$sum' => 1
                    ]
                ]
            ]
        ], [
            '$sort' => [
                'date' => 1
            ],
            '$limit' => 24
        ]);

        // 7 days stats
        $createdWithin7days = $collection->aggregate([
            [
                '$group' => [
                    '_id' => [
                        'month' => [
                            '$month' => '$date'
                        ],
                        'day' => [
                            '$dayOfMonth' => '$date'
                        ],
                    ],
                    'count' => [
                        '$sum' => 1
                    ]
                ]
            ]
        ], [
            '$sort' => [
                'date' => 1
            ],
            '$limit' => 7
        ]);

        // Last 60 minutes
        $createdWithin60minutes = $collection->aggregate([
            [
                '$group' => [
                    '_id' => [
                        'month' => [
                            '$month' => '$date'
                        ],
                        'day' => [
                            '$dayOfMonth' => '$date'
                        ],
                        'hour' => [
                            '$hour' => '$date'
                        ],
                        'minute' => [
                            '$minute' => '$date'
                        ]
                    ],
                    'count' => [
                        '$sum' => 1
                    ]
                ]
            ]
        ], [
            '$sort' => [
                'date' => 1
            ],
            '$limit' => 60
        ]);

        // Last 60 minutes
        $createdWithinLastMonth = $collection->aggregate([
            [
                '$group' => [
                    '_id' => [
                        'month' => [
                            '$month' => '$date'
                        ],
                        'day' => [
                            '$dayOfMonth' => '$date'
                        ]
                    ],
                    'count' => [
                        '$sum' => 1
                    ]
                ]
            ]
        ], [
            '$sort' => [
                'date' => 1
            ],
            '$limit' => 31
        ]);

        return $this->render(':error:statsFixes.html.twig', [
            'createdWithin24Hours' => $createdWithin24hours->toArray(),
            'createdWithin7Days' => $createdWithin7days->toArray(),
            'createdWithin60minutes' => $createdWithin60minutes->toArray(),
            'createdWithinLastMonth' => $createdWithinLastMonth->toArray()
        ]);
    }

    /**
     * @Route("/stats", name="error_stats")
     * @Method("GET")
     */
    public function statsAction()
    {
        $collection = $this->getDataProvider();

        // 24 hours stats
        $createdWithin24hours = $collection->aggregate([
            [
                '$group' => [
                    '_id' => [
                        'month' => [
                            '$month' => '$occurred'
                        ],
                        'day' => [
                            '$dayOfMonth' => '$occurred'
                        ],
                        'hour' => [
                            '$hour' => '$occurred'
                        ]
                    ],
                    'count' => [
                        '$sum' => 1
                    ]
                ]
            ]
        ], [
            '$sort' => [
                'occurred' => 1
            ],
            '$limit' => 24
        ]);

        // 7 days stats
        $createdWithin7days = $collection->aggregate([
            [
                '$group' => [
                    '_id' => [
                        'month' => [
                            '$month' => '$occurred'
                        ],
                        'day' => [
                            '$dayOfMonth' => '$occurred'
                        ],
                    ],
                    'count' => [
                        '$sum' => 1
                    ]
                ]
            ]
        ], [
            '$sort' => [
                'occurred' => 1
            ],
            '$limit' => 7
        ]);

        // Last 60 minutes
        $createdWithin60minutes = $collection->aggregate([
            [
                '$group' => [
                    '_id' => [
                        'month' => [
                            '$month' => '$occurred'
                        ],
                        'day' => [
                            '$dayOfMonth' => '$occurred'
                        ],
                        'hour' => [
                            '$hour' => '$occurred'
                        ],
                        'minute' => [
                            '$minute' => '$occurred'
                        ]
                    ],
                    'count' => [
                        '$sum' => 1
                    ]
                ]
            ]
        ], [
            '$sort' => [
                'occurred' => 1
            ],
            '$limit' => 60
        ]);

        // Last 60 minutes
        $createdWithinLastMonth = $collection->aggregate([
            [
                '$group' => [
                    '_id' => [
                        'month' => [
                            '$month' => '$occurred'
                        ],
                        'day' => [
                            '$dayOfMonth' => '$occurred'
                        ]
                    ],
                    'count' => [
                        '$sum' => 1
                    ]
                ]
            ]
        ], [
            '$sort' => [
                'occurred' => 1
            ],
            '$limit' => 31
        ]);

        return $this->render(':error:stats.html.twig', [
            'createdWithin24Hours' => $createdWithin24hours->toArray(),
            'createdWithin7Days' => $createdWithin7days->toArray(),
            'createdWithin60minutes' => $createdWithin60minutes->toArray(),
            'createdWithinLastMonth' => $createdWithinLastMonth->toArray()
        ]);
    }

    /**
     * @Route("/clear/{app}/{env}", name="error_clear")
     * @Method("GET")
     */
    public function clearAction($app, $env, Request $request)
    {
        $fixes = new ErrorFixes();
        $fixes->date = new \DateTime('now');
        $fixes->count = $this->getDataProvider()->deleteMany([
            'app' => $app,
            'env' => $env
        ])->getDeletedCount();

        $this->get('mongodb_provider')->getCollection('error_fixes')->insertOne($fixes);

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }

    /**
     * @Route("/clear/index", name="error_clear_index")
     * @Method("GET")
     */
    public function clearViewAction()
    {
        $data = [];
        $apps = $this->getDataProvider()->distinct('app');
        foreach ($apps as $app) {
            $data[$app] = $this->getDataProvider()->distinct('env', ['app' => $app]);
        }

        return $this->render(':error:clear.html.twig', [
            'applications' => $data
        ]);
    }

    /**
     * @Route("/fix_like_this/{id}", name="error_fix_like_this")
     * @Method("GET")
     */
    public function fixLikeThisAction($id, Request $request)
    {
        $error = $this->getDataProvider()->findOne(['_id' => new ObjectID($id)]);
        if (null === $error) {
            throw new NotFoundHttpException('Error with identifier not found');
        }

        $fixes = new ErrorFixes();
        $fixes->date = new \DateTime('now');
        $fixes->count = $this->getDataProvider()->deleteMany([
            'message' => $error->message,
            'app' => $error->app,
            'env' => $error->env
        ])->getDeletedCount();

        $this->get('mongodb_provider')->getCollection('error_fixes')->insertOne($fixes);

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }
}
