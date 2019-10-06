<?php

class ItemController {

    /**
     * Add new item to items table in the database.
     */
    function addItem($request, $response, $args) {
        // check required params
        if (!hasRequiredParams(array('item'), $response)) {
            return $response;
        }
    
        // reading post params
        $request_data = $request->getParams();
        $item = $request_data['item'];
        global $user_id;
    
        // add item
        $db = new DbOperations();
        $result = $db->addItem($user_id, $item);
    
        if ($result == ITEM_ADDED_SUCCESSFULLY) {
            $message['error'] = false;
            $message['message'] = "Item added successfully";
            return buildResponse(201, $message, $response);
        } else {
            $message['error'] = true;
            $message['message'] = "Failed to add item. Please try again";
            return buildResponse(200, $message, $response);
        }
    }

    /**
     * Get single item from items table in the database.
     */
    function getItem($request, $response, $args) {
        $item_id = $args['id'];
        global $user_id;
    
        // get single item
        $db = new DbOperations();
        $result = $db->getItem($user_id, $item_id);
    
        if ($result != null) {
            $item_details = array();
            $item_details['id'] = $result['id'];
            $item_details['user_id'] = $result['user_id'];
            $item_details['item'] = $result['item'];
            $item_details['status'] = $result['status'];
            $item_details['created_at'] = $result['created_at'];
    
            $message['error'] = false;
            $message['item'] = $item_details;
            return buildResponse(200, $message, $response);
        } else {
            $message['error'] = true;
            $message['message'] = "Requested item not found";
            return buildResponse(404, $message, $response);
        }
    }

    /**
     * Get items associated with the user from the database.
     * Client may pass status param to further filter the items.
     * Status 1 for completed items and 0 for active items.
     */
    function getItems($request, $response, $args) {
        // reading post params
        $request_data = $request->getParams();
        global $user_id;
        $status = -1;
        if (isset($request_data['status'])) {
            $status = $request_data['status'];
            // check valid status code
            if ($status != 1 && $status != 0) {
                $message['error'] = true;
                $message['message'] = "Invalid status code. Should be either 1(completed) or 0(active)";
                return buildResponse(400, $message, $response);
            }
        }
    
        // get all items associated with the user
        $db = new DbOperations();
        $result = $db->getItems($user_id, $status);
    
        $message['error'] = false;
        $message['items'] = array();
    
        // looping through result and preparing items array
        while ($item = $result->fetch_assoc()) {
            $item_details = array();
            $item_details['id'] = $item['id'];
            $item_details['user_id'] = $item['user_id'];
            $item_details['item'] = $item['item'];
            $item_details['status'] = $item['status'];
            $item_details['created_at'] = $item['created_at'];
            array_push($message['items'], $item_details);
        }
    
        return buildResponse(200, $message, $response);
    }

    /**
     * Update item in the items table in the database.
     */
    function updateItem($request, $response, $args) {
        $item_id = $args['id'];
    
        // check required params
        if (!hasRequiredParams(array('item'), $response)) {
            return $response;
        }
    
        // reading post params
        $request_data = $request->getParams();
        global $user_id;
        $item = $request_data['item'];
    
        // update item
        $db = new DbOperations();
        if ($db->updateItem($user_id, $item_id, $item)) {
            $message['error'] = false;
            $message['message'] = "Item updated successfully";
        } else {
            $message['error'] = true;
            $message['message'] = "Failed to update item. Please try again";
        }
    
        return buildResponse(200, $message, $response);
    }

    /**
     * Update item status.
     * Status 1 to mark as completed and 0 as active respectively.
     */
    function updateStatus($request, $response, $args) {
        $item_id = $args['id'];
        $status = $args['code'];
        global $user_id;
    
        // check valid status code
        if ($status != 1 && $status != 0) {
            $message['error'] = true;
            $message['message'] = "Invalid status code. Should be either 1(completed) or 0(active)";
            return buildResponse(400, $message, $response);
        }
    
        // update status of the item
        $db = new DbOperations();
        if ($db->updateStatus($user_id, $item_id, $status)) {
            $message['error'] = false;
            if ($status == 1) {
                $message['message'] = "Item marked completed successfully";
            } else {
                $message['message'] = "Item marked active successfully";
            }
        } else {
            $message['error'] = true;
            $message['message'] = "Failed to update status of the item. Please try again";
        }
    
        return buildResponse(200, $message, $response);
    }

    /**
     * Delete single item from the items table in the database.
     */
    function deleteItem($request, $response, $args) {
        $item_id = $args['id'];
        global $user_id;
        // delete item
        $db = new DbOperations();
        if ($db->deleteItem($user_id, $item_id)) {
            $message['error'] = false;
            $message['message'] = "Item deleted successfully";
        } else {
            $message['error'] = true;
            $message['message'] = "Failed to delete item. Please try again";
        }
    
        return buildResponse(200, $message, $response);
    }

    /**
     * Delete all items associated with the user from 
     * the items table in the database.
     */
    function deleteItems($request, $response, $args) {
        global $user_id;
        // delete all items of the user
        $db = new DbOperations();
        if ($db->deleteItems($user_id)) {
            $message['error'] = false;
            $message['message'] = "All items were deleted successfully";
        } else {
            $message['error'] = true;
            $message['message'] = "Failed to delete all items. Please try again";
        }
    
        return buildResponse(200, $message, $response);
    }

    /**
     * Delete completed items from the items table in the database.
     */
    function clearCompleted($request, $response, $args) {
        global $user_id;
        // delete completed items of the user
        $db = new DbOperations();
        if ($db->deleteCompletedItems($user_id)) {
            $message['error'] = false;
            $message['message'] = "Completed items were deleted successfully";
        } else {
            $message['error'] = true;
            $message['message'] = "You have no completed items. All items are active";
        }
    
        return buildResponse(200, $message, $response);
    }
    
}

?>