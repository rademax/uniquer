# Getting started

## Installation

To deploy the project you need to perform next commands in project directory:

- Install project dependencies (for avoid the installing Composer globally.)
```
docker run --rm -v $(pwd):/app composer install
```
- Create .env file
```
cp .env.example .env
```
- Start containers
```
docker-compose up -d
```
- Perform migrations to database
```
docker-compose exec app php artisan migrate
```
Finally, you can access the application on http://localhost:80

## Testing
To perform tests, run command:
```
docker-compose exec app ./vendor/bin/phpunit
```

## API Documentation

**Get unique article list**
----
  Returns json unique article list data.

* **URL**

  /articles

* **Method:**

  `GET`
  
*  **URL Params**

   `page=[integer]`
   `per_page=[integer]`

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** 
```
    {
        data: [
            { 
                id : 12, 
                content : "...", 
                duplicate_article_ids: [1, 3] 
            }
        ]
    }
```

**Get article**
----
  Returns article json data.

* **URL**

  /articles/:id

* **Method:**

  `GET`
  
*  **URL Params**

   **Required:**
 
   `id=[integer]`

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** 
```
    {
        id : 12, 
        content : "...", 
        duplicate_article_ids: [1, 3] 
    }
```
    
* **Error Response:**

  * **Code:** 404 NOT FOUND <br />
    **Content:** `{ error : "Article doesn't exist" }`
    
**Create an article**
----
  Returns article json data.

* **URL**

  /articles

* **Method:**

  `POST`
  
*  **URL Params**

   **Required:**
 
   `content=[string]`

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** 
```
    {
        id : 12, 
        content : "...", 
        duplicate_article_ids: [1, 3] 
    }
```
    
* **Error Response:**

  * **Code:** 422 <br />
    **Content:** `{ error : "The attribute Content is required field" }`

**Get duplicate group list**
----
  Returns json duplicate group list data.

* **URL**

  /duplicate_groups

* **Method:**

  `GET`
  
*  **URL Params**

   `page=[integer]`
   `per_page=[integer]`

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** 
```
    {
        duplicate_groups: [
            [1, 12, 15],
            [5, 7, 10],
        ]
    }
```

## Methodology

The shingle algorithm was used.

As far as it is concerned, it is used by search engines to find duplicates

The stages that the text undergoing comparison goes through:
- canonization of the text;
- splitting into shingles;
- calculation of shingle hashes;
- a random sample of 84 checksum values;
- comparison, determination of the result.

The principle of the shingle algorithm is to compare a random sample of shingle checksums (subsequences) of two texts with each other.

It was implemented:
- removal of special characters,
- search and delete words (for example, articles a, the)
- normalization of words (irregular verbs)

## Next steps

In the future, it is planned to normalize words that include the endings 'ed', 'ing' and the like.

Also, given that the service will expand, it will be necessary to optimize and save a set of shingles in the database for each article.
