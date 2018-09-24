<?php
namespace LeoGalleguillos\User\Controller\ResetPassword;

use Exception;
use LeoGalleguillos\Flash\Model\Service as FlashService;
use LeoGalleguillos\User\Model\Table as UserTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class Code extends AbstractActionController
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $userId;

    public function __construct(
        FlashService\Flash $flashService,
        UserTable\ResetPassword $resetPasswordTable,
        UserTable\ResetPasswordAccessLog $resetPasswordAccessLogTable,
        UserTable\User\PasswordHash $passwordHashTable
    ) {
        $this->flashService                = $flashService;
        $this->resetPasswordTable          = $resetPasswordTable;
        $this->resetPasswordAccessLogTable = $resetPasswordAccessLogTable;
        $this->passwordHashTable           = $passwordHashTable;
    }

    public function indexAction()
    {
        $count = $this->resetPasswordAccessLogTable
                      ->selectCountWhereIpAndValidAndCreatedGreaterThan(
                          $_SERVER['REMOTE_ADDR'],
                          0,
                          date('Y-m-d H:i:s', strtotime('-1 day'))
                      );
        if ($count >= 3) {
            return $this->redirect()->toRoute('reset-password')->setStatusCode(303);
        }

        $this->code = $this->params()->fromRoute('code');
        try {
            $this->userId = $this->resetPasswordTable->selectUserIdWhereCode(
                $this->code
            );
        } catch (Exception $exception) {
            $this->resetPasswordAccessLogTable->insert(
                $_SERVER['REMOTE_ADDR'],
                0
            );
            return $this->redirect()->toRoute('reset-password')->setStatusCode(303);
        }

        if (!empty($_POST)) {
            return $this->postAction();
        }

        $this->resetPasswordAccessLogTable->insert(
            $_SERVER['REMOTE_ADDR'],
            1
        );

        return [
            'errors' => $this->flashService->get('errors'),
        ];
    }

    protected function postAction()
    {
        $errors = [];
        if (empty($_POST['new_password'])) {
            $errors[] = 'Invalid new password.';
        }
        if ($_POST['new_password'] != $_POST['confirm_new_password']) {
            $errors[] = 'New password and confirm new password do not match.';
        }

        if ($errors) {
            $this->flashService->set('errors', $errors);
            $parameters = [
                'code' => $this->code,
            ];
            return $this->redirect()->toRoute('reset-password/code', $parameters)->setStatusCode(303);
        }

        $this->passwordHashTable->updateWhereUserId(
            password_hash($_POST['new_password'], PASSWORD_DEFAULT),
            $this->userId
        );

        $viewModel = new ViewModel();
        $viewModel->setTemplate('leo-galleguillos/user/reset-password/code/success');
        return $viewModel;
    }
}
