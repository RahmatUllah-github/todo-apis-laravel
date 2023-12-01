<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Services\JsonResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $authId = auth()->id();
            $paginationLimit = config('custom.pagination_limit'); // get pagination limit from config

            $todos = Todo::where('user_id', $authId)->paginate($paginationLimit);

            return JsonResponseService::successResponse('Here is your todos list', $todos);

        } catch (Throwable $th) {
            Log::info('**************************** Error in TodoController index method ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'title' => ['required', 'string', maxString()],
                'description' => ['required', 'string'],
            ];

            $data = $request->all();

            // Validate the incoming request data
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $message = 'Please fill the form correctly.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            $authId = auth()->id();
            $data['user_id'] = $authId;

            $todo = Todo::create($data);

            return JsonResponseService::successResponse('Todo added successfully.', $todo);

        } catch (Throwable $th) {
            if ($todo) {
                $todo->delete(); // if todo is created and then something went wrong then delete
            }

            Log::info('**************************** Error in TodoController store method ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $rules = [
                'id' => ['required', 'exists:todos,id'],
            ];

            $data = ['id' => $id];
            $messages = [
                'id.exists' => 'The requested todo does not exist.' // custom error message
            ];

            // Validate the incoming request data
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $message = 'Please send the correct todos id.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            $todo = Todo::find($id);
            $authId = auth()->id();

            if ($todo->user_id != $authId) { // check if the todo belongs to the authenticated user
                return JsonResponseService::unauthorizedErrorResponse('You are not authorized to access this todo.');
            }

            return JsonResponseService::successResponse('Here is your todo.', $todo);

        } catch (Throwable $th) {
            Log::info('**************************** Error in TodoController show method ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $rules = [
                'title' => ['required', 'string', maxString()],
                'description' => ['required', 'string'],
                'id' => ['required', 'exists:todos,id'],
            ];

            $data = $request->all();
            $data['id'] = $id;
            $messages = [
                'id.exists' => 'The requested todo does not exist.'
            ];

            // Validate the incoming request data
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $message = 'Please fill the form correctly.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            $todo = Todo::find($id);
            $authId = auth()->id();

            if ($todo->user_id != $authId) { // check if the user is authorized to update the todo
                return JsonResponseService::unauthorizedErrorResponse('You are not authorized to update this todo.');
            }

            $todo->update($data);

            return JsonResponseService::successResponse('Todo updated successfully.', $todo);
        } catch (Throwable $th) {
            Log::info('**************************** Error in TodoController update method ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $rules = [
                'id' => ['required', 'exists:todos,id'],
            ];

            $data = ['id' => $id];
            $messages = [
                'id.exists' => 'The requested todo does not exist.'
            ];

            // Validate the incoming request data
            $validator = Validator::make($data, $rules, $messages);

            if ($validator->fails()) {
                $message = 'Please send the correct todos id.';
                return JsonResponseService::validationErrorResponse($message, $validator->errors()->all());
            }

            $todo = Todo::find($id);
            $authId = auth()->id();

            if ($todo->user_id != $authId) { // check if the user is authorized to delete the todo
                return JsonResponseService::unauthorizedErrorResponse('You are not authorized to delete this todo.');
            }

            $todo->delete();

            return JsonResponseService::successResponse('Todo deleted successfully.');

        } catch (Throwable $th) {
            Log::info('**************************** Error in TodoController destroy method ********************************');
            Log::info('Error is on line: '. $th->getLine());
            Log::error($th->getMessage());
            return JsonResponseService::errorResponse('Something went wrong. Please try again.');
        }
    }
}
