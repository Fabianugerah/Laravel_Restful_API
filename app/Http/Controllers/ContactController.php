<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContactCreateRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use Illuminate\Database\Eloquent\Builder;


class ContactController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/contacts",
     *     tags={"Contacts"},
     *     summary="Create new contact",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Create new contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Fabianugerah"),
     *             @OA\Property(property="last_name", type="string", example="Bainasshiddiq"),
     *             @OA\Property(property="email", type="string", example="fabianugerah@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="088217418481")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success create contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="first_name", type="string", example="Fabianugerah"),
     *             @OA\Property(property="last_name", type="string", example="Bainasshiddiq"),
     *             @OA\Property(property="email", type="string", example="fabianugerah@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="088217418481")
     *             )
     *         )
     *     )
     * )
     */
    public function create(ContactCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $contact = new Contact($data);
        $contact->user_id = $user->id;
        $contact->save();

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    // GET CONTACTS
    /**
     * @OA\Get(
     *     path="/api/contacts/{id}",
     *     tags={"Contacts"},
     *     summary="Get contact detail",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Success get contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function get(int $id): ContactResource
    {
        $user = Auth::user();
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'massage' => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        return new ContactResource($contact);
    }



    /**
     * @OA\Put(
     *     path="/api/contacts/{id}",
     *     tags={"Contacts"},
     *     summary="Update contact",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Update contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success update contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function update(int $id, ContactUpdateRequest $request): ContactResource
    {
        $user = Auth::user();

        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'massage' => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        $data = $request->validated();
        $contact->fill($data);
        $contact->save();

        return new ContactResource($contact);
    }

    /**
     * @OA\Delete(
     *     path="/api/contacts/{id}",
     *     tags={"Contacts"},
     *     summary="Delete contact",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Success delete contact",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();

        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'massage' => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        $contact->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    // SEARCH CONTACTS
    /**
     * @OA\Get(
     *     path="/api/contacts",
     *     tags={"Contacts"},
     *     summary="Search contacts",
     *     @OA\Parameter(name="name", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="phone", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="email", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="size", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Success search contacts",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="first_name", type="string", example="Fabianugerah"),
     *                  @OA\Property(property="last_name", type="string", example="Bainasshiddiq"),
     *                  @OA\Property(property="email", type="string", example="fabianugerah@gmail.com"),
     *                  @OA\Property(property="phone", type="string", example="088217418481")
     *             )),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function search(Request $request): ContactCollection
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        $contacts = Contact::query()->where('user_id', $user->id);

        $contacts = $contacts->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('first_name', 'like', '%' . $name . '%');
                    $builder->orWhere('last_name', 'like', '%' . $name . '%');
                });
            }

            $email = $request->input('email');
            if ($email) {
                $builder->where('email', 'like', '%' . $email . '%');
            }

            $phone = $request->input('phone');
            if ($phone) {
                $builder->where('phone', 'like', '%' . $phone . '%');
            }
        });

        $contacts = $contacts->paginate(perPage: $size, page: $page);

        return new ContactCollection($contacts);
    }
}
