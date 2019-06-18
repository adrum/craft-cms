<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\controllers;

use Craft;
use craft\elements\GlobalSet;
use craft\errors\MissingComponentException;
use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\helpers\MailerHelper;
use craft\helpers\UrlHelper;
use craft\mail\Mailer;
use craft\mail\transportadapters\BaseTransportAdapter;
use craft\mail\transportadapters\Sendmail;
use craft\mail\transportadapters\TransportAdapterInterface;
use craft\models\MailSettings;
use craft\web\assets\generalsettings\GeneralSettingsAsset;
use craft\web\Controller;
use craft\web\twig\TemplateLoaderException;
use DateTime;
use GraphQL\GraphQL;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * The GqlController class is a controller that handles various GraphQL related tasks.
 * @TODO Docs
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.2
 */
class GqlController extends Controller
{
    // Public Methods
    // =========================================================================

    public $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // All system setting actions require an admin
//        $this->requireAdmin();
    }

    /**
     * Shows the general settings form.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $schema = Craft::$app->getGql()->getSchema(Craft::$app->getConfig()->getGeneral()->devMode);

        if (Craft::$app->request->isPost && $query=Craft::$app->request->post('query')) {
            $input = $query;
        } else if (Craft::$app->request->isGet && $query=Craft::$app->request->get('query')) {
            $input = $query;
        } else {
            $data = Craft::$app->request->getRawBody();
            $data = json_decode($data, true);
            $input = @$data['query'];
        }

        if ($input) {
            $result = GraphQL::executeQuery($schema, $input, null, null, null)->toArray(true);
        } else {
            throw new BadRequestHttpException('Request missing required param');
        }

        return $this->asJson($result);
    }
}
