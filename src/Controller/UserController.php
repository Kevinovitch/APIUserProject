<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Serializer\FormErrorSerializer;
use App\Service\UserService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/***
 *  API to get some data concerning users
 */
#[Route('/api')]
class UserController extends AbstractController
{

    private UserService $userService;
    private FormErrorSerializer $formErrorSerializer;

    public function __construct(
        UserService $userService,
        FormErrorSerializer $formErrorSerializer
    )
    {
        $this->userService = $userService;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Function to show all the users
     *
     * @return JsonResponse
     */
    #[Route('/users', name: 'app_user_list', methods: ['GET'])]
    public function userListAction(): JsonResponse
    {

        $userList = $this->userService->fetchAllUsers();

        return new JsonResponse($userList, Response::HTTP_OK);


    }

    /**
     * Method to show one user that we get by its id
     *
     * @param $id
     * @return JsonResponse|void
     */
    #[Route('/users/{id}', name: 'app_user_show', requirements: ["id"=>"\d+"], methods: ['GET'])]
    public function userShowAction($id)
    {

        try {
            $user  = $this->userService->fetchUserById($id);

            if (!$user == null) {
                return new JsonResponse($user, Response::HTTP_OK);
            }
        }
        catch (\Exception $e)
        {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }


    }


    /**
     * Method to create a new User
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/users/new', name: 'app_user_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        // We collect the data of the POST request and turn them into
        // an associative array
        $userDatas = json_decode($request->getContent(), true );

        // We use the UserType to validate theses datas
        $form = $this->createForm(UserType::class, new User());
        $form->submit($userDatas);

        // If there are false we return a 400 response
        if(false === $form->isValid()){
            return new JsonResponse([
                'status' => 'error',
                'errors' => $this->formErrorSerializer->convertFormToArray($form),
            ],
                Response::HTTP_BAD_REQUEST);
        }

        // If there are right, we use these datas to create a new
        // User Entity with the saveUserCreated()
        $userCreated = $form->getData();

        $this->userService->saveUserCreated($userCreated);



        return new JsonResponse(['status' => 'ok'], Response::HTTP_CREATED);


    }


    #[Route('/users/{id}', name: 'app_user_edit', requirements: ["id"=>"\d+"], methods: ['PUT'])]
    public function updateUserAction(Request $request, $id): Response
    {
        try{

            $data = json_decode($request->getContent(), true);

            $existingUser = $this->userService->fetchUserById($id);

            $form = $this->createForm(UserType::class, $existingUser);
            $form->submit($data);

            if(false === $form->isValid()){
                return new JsonResponse([
                    'status' => 'error',
                    'errors' => $this->formErrorSerializer->convertFormToArray($form),
                ],
                    Response::HTTP_BAD_REQUEST);
            }

            $userUpdated = $form->getData();

            $this->userService->saveUserUpdated($userUpdated);



            return new JsonResponse(null, Response::HTTP_NO_CONTENT);

        }catch (\Exception $e)
        {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

    }

    #[Route('/users/{id}', name: 'app_user_delete', requirements: ["id"=>"\d+"],  methods: ['DELETE'])]
    public function deleteUserAction($id): Response
    {
        try
        {
            $existingUser = $this->userService->fetchUserById($id);

            $this->userService->deleteUser($existingUser);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        catch(\Exception $e)
        {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }


    }
}
