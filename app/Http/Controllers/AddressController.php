<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Contact;
use App\Models\Address;
use App\Models\User;
use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Resources\AddressResource;

class AddressController extends Controller
{
    private function getContact(User $user, int $idContact): Contact
    {
        $contact = Contact::where('user_id', $user->id)->where('id', $idContact)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'massage' => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }
        return $contact;
    }

    private function getAddress(Contact $contact, int $idAddress): Address
    {
        $address = Address::where('contact_id', $contact->id)
            ->where('id', $idAddress)
            ->first();

        if (!$address) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        "not found"
                    ]
                ]
            ], 404));
        }

        return $address;
    }


    // CREATE ADDRESS
    /**
     * @OA\Post(
     *     path="/api/contacts/{idContact}/addresses",
     *     summary="Create new address",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="idContact", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"street", "city", "province", "country", "postal_code"},
     *             @OA\Property(property="street", type="string", example="Dharma"),
     *             @OA\Property(property="city", type="string", example="Pandaan"),
     *             @OA\Property(property="province", type="string", example="Jawa Timur"),
     *             @OA\Property(property="country", type="string", example="Indonesia"),
     *             @OA\Property(property="postal_code", type="string", example="67156")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Success create address")
     * )
     */
    public function create(int $idContact, AddressCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);

        $data = $request->validated();
        $address = new Address($data);
        $address->contact_id = $contact->id;
        $address->save();

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    // GET ID ADDRESS
    /**
     * @OA\Get(
     *     path="/api/contacts/{idContact}/addresses/{idAddress}",
     *     summary="Get single address",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="idContact", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="idAddress", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Address detail")
     * )
     */
    public function get(int $idContact, int $idAddress): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);
        $address = $this->getAddress($contact, $idAddress);

        return new AddressResource($address);
    }

    // UPDATE ADDRESS
    /**
     * @OA\Put(
     *     path="/api/contacts/{idContact}/addresses/{idAddress}",
     *     summary="Update address",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="idContact", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="idAddress", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="street", type="string", example="Dharma Bakti"),
     *             @OA\Property(property="city", type="string", example="Pasuruan"),
     *             @OA\Property(property="province", type="string", example="Jatim"),
     *             @OA\Property(property="country", type="string", example="Indonesia"),
     *             @OA\Property(property="postal_code", type="string", example="67156")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success update address")
     * )
     */
    public function update(int $idContact, int $idAddress, AddressUpdateRequest $request): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);
        $address = $this->getAddress($contact, $idAddress);

        $data = $request->validated();
        $address->fill($data);
        $address->save();

        return new AddressResource($address);
    }

    // DELETE ADDRESS
    /**
     * @OA\Delete(
     *     path="/api/contacts/{idContact}/addresses/{idAddress}",
     *     summary="Delete address",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="idContact", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="idAddress", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success delete address")
     * )
     */
    public function delete(int $idContact, int $idAddress): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);
        $address = $this->getAddress($contact, $idAddress);
        $address->delete();

        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    // GET LIST ADDRESSES
    /**
     * @OA\Get(
     *     path="/api/contacts/{idContact}/addresses",
     *     summary="Get list of addresses",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="idContact", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List addresses")
     * )
     */
    public function list(int $idContact): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);
        $addresses = Address::where('contact_id', $contact->id)->get();
        return (AddressResource::collection($addresses))->response()->setStatusCode(200);
    }
}
