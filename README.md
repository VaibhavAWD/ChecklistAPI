# Checklist API

Checklist is a application which allows users to create account and add list of items and interact with them.

## Endpoints

Following the is list of endpoints available to fetch and interact with the data.

### User related endpoints

#### Register User

| URL    | /register             |
| ------ | :-------------------- |
| Method | POST                  |
| Params | name, email, password |

#### Login User

| URL    | /login          |
| ------ | :-------------- |
| Method | POST            |
| Params | email, password |



### Item related endpoints

For all item related endpoints you must supply the Api key to authenticate the operations. Each user in the application get unique Api key.

#### Add Item

| URL    | /items |
| :----- | :----- |
| Method | POST   |
| Params | item   |

#### Get Item

| URL    | /items{id} |
| ------ | ---------- |
| Method | GET        |
| Params | -          |

#### Get Items

| URL    | /items |
| ------ | ------ |
| Method | GET    |
| Params | status |

Here the param status is optional. But if the you want to filter the list of items to show only completed or only active items in the database then pass 1 to show complete and 0 to show active items.

#### Update Item

| URL    | /items/{id} |
| ------ | ----------- |
| Method | PUT         |
| Params | item        |

#### Update Status

| URL    | /items/{id}/status{code} |
| ------ | ------------------------ |
| Method | PUT                      |
| Params | -                        |

The status code should be either 1 or 0 to mark the item completed or active respectively.

#### Delete Item

| URL    | /items/{id} |
| ------ | ----------- |
| Method | DELETE      |
| Params | -           |

#### Delete Items

| URL    | /items |
| ------ | ------ |
| Method | DELETE |
| Params | -      |

#### Delete Completed Items

| URL    | /clearcompleted |
| ------ | --------------- |
| Method | DELETE          |
| Params | -               |

