# Filipino Cookbook API

## 1. API Description
The Filipino Cookbook API is a RESTful web service developed using the Slim Framework (PHP) and MySQL. It allows users to manage and retrieve information about traditional Filipino dishes, including their category, origin, ingredients, and cooking instructions. The API communicates using JSON and uses token-based authentication for protected endpoints.

---

## 2. Features
- Welcome API route
- Retrieve all Filipino dishes
- Retrieve a specific food by ID
- Add a new Filipino dish
- Update existing food information
- Delete food records
- JSON request and response format
- Token-based authentication
- PDO database connection
- RESTful API architecture

---

## 3. Technologies Used
| Technology | Purpose |
|------------|---------|
| PHP 8.x | Backend Programming Language |
| Slim Framework 4 | REST API Framework |
| Composer | Dependency Manager |
| MySQL | Database Management |
| PDO | Secure Database Connection |
| Apache (XAMPP) | Local Web Server |
| JSON | Data Exchange Format |
| Thunder Client / Postman | API Testing |

---

## 4. Installation Instructions
1. Clone the repository:
```bash
git clone https://github.com/YOUR_USERNAME/filipino-cookbook-api.git
```
2. Open the project folder:
```bash
cd filipino-cookbook-api
```
3. Install dependencies:
```bash
composer install
```
4. Start Apache and MySQL using XAMPP.
5. Import the SQL database into phpMyAdmin.

---

## 5. Database Setup
Create a database named:
```
filipino_cookbook_api
```
Import the provided SQL file and configure the database connection in `public/index.php`.

---

## 6. Base URL
```
http://localhost/filipino-cookbook-api/public
```

---

## 7. Authentication Instructions
Use the Authorization header:
```
Authorization: Bearer dmmmsu-cookbook-2026
```
Replace the token if your project uses a different one.

---

## 8. Endpoint Documentation

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Welcome message |
| GET | `/api/foods` | Retrieve all foods |
| GET | `/api/foods/{id}` | Retrieve a food by ID |
| POST | `/api/foods` | Add a new food |

### Sample POST Request
```json
{
  "food_name":"Adobo",
  "category":"Main Dish",
  "origin":"Luzon",
  "ingredients":"Chicken, Soy Sauce, Vinegar",
  "instructions":"Mix ingredients and simmer until cooked."
}
```

### Sample Response
```json
{
  "status":"success",
  "message":"Food added successfully."
}
```

---

## 9. HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 500 | Internal Server Error |

---

## 10. Testing Evidence

Test the API using:
- Thunder Client
- Postman

*GET Request:*
![GET Request](pic/GET.png)

*POST Request:*
![POST Request](pic/POST.png)

---

## 11. Developer Information

**Project:** Filipino Cookbook API

**Student:** Peter Terence Canete

**Course:** Bachelor of Science in Information Technology

**Github Username:**  peterterence
