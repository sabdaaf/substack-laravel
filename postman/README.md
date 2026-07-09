# Postman Collection for Foogu CMS API

## 📦 Files Included

1. **Foogu-CMS-API.postman_collection.json** - Complete API collection with all endpoints
2. **Foogu-CMS-API.postman_environment.json** - Environment variables for local development

## 🚀 Quick Start

### 1. Import Collection & Environment

1. Open Postman
2. Click **Import** button
3. Select both files:
    - `Foogu-CMS-API.postman_collection.json`
    - `Foogu-CMS-API.postman_environment.json`
4. Click **Import**

### 2. Select Environment

1. In Postman, click the environment dropdown (top right)
2. Select **"Foogu CMS API - Local"**
3. Verify `base_url` is set to `http://localhost:8000`

### 3. Start Laravel Server

```bash
php artisan serve
```

## 🔐 Authentication Flow

### Automatic Token Management

The collection has **automatic token saving** built-in:

1. **Register** or **Login** request will automatically:

    - Save the token to `{{auth_token}}` environment variable
    - Display confirmation in Postman Console
    - Apply to all protected endpoints automatically

2. All protected endpoints use `{{auth_token}}` automatically via Bearer Auth

### Testing Authentication

**Recommended order:**

1. ✅ **Register** - Create new user
    - Token automatically saved ✓
2. ✅ **Get Current User (Me)** - Verify authentication works
3. ✅ **Create Post** - Test authenticated endpoint

    - Post slug automatically saved to `{{post_slug}}` ✓

4. ✅ **Get Single Post by Slug** - Uses saved `{{post_slug}}`

5. ✅ **Update/Delete Post** - Uses saved `{{post_slug}}`

## 📝 Available Endpoints

### Authentication (4 endpoints)

-   `POST /api/register` - Register new user (auto-saves token)
-   `POST /api/login` - Login (auto-saves token)
-   `POST /api/logout` - Logout (requires auth)
-   `GET /api/me` - Get current user (requires auth)

### Posts - Public (5 endpoints)

-   `GET /api/posts` - Get all posts with pagination, filtering, sorting
-   `GET /api/posts/{slug}` - Get single post by slug
-   `GET /api/posts?search={keyword}` - Search posts
-   `GET /api/posts?author_id={uuid}` - Filter by author
-   `GET /api/posts?sort_by=title&order=asc` - Sort posts

### Posts - Protected (5 endpoints)

All require authentication:

-   `POST /api/posts` - Create post (auto-saves slug)
-   `POST /api/posts` - Create post with custom slug
-   `PUT /api/posts/{slug}` - Full update post
-   `PATCH /api/posts/{slug}` - Partial update post
-   `DELETE /api/posts/{slug}` - Delete post

## 🎯 Environment Variables

The environment automatically manages these variables:

| Variable     | Description            | Auto-filled              |
| ------------ | ---------------------- | ------------------------ |
| `base_url`   | API base URL           | ❌ Manual                |
| `auth_token` | Bearer token           | ✅ Auto (login/register) |
| `post_slug`  | Last created post slug | ✅ Auto (create post)    |
| `author_id`  | User UUID              | ❌ Manual                |

## 📊 Query Parameters Reference

### GET /api/posts

| Parameter   | Type    | Default    | Description                                         |
| ----------- | ------- | ---------- | --------------------------------------------------- |
| `per_page`  | integer | 10         | Items per page                                      |
| `page`      | integer | 1          | Page number                                         |
| `sort_by`   | string  | created_at | Sort field: id, title, slug, created_at, updated_at |
| `order`     | string  | desc       | Sort order: asc, desc                               |
| `author_id` | UUID    | -          | Filter by author                                    |
| `search`    | string  | -          | Search in title or body                             |
| `slug`      | string  | -          | Filter by specific slug                             |

## 🧪 Testing Scenarios

### Scenario 1: Complete Auth Flow

1. Register → Token saved automatically ✓
2. Get Current User → Verify token works
3. Logout → Token still works (revoked on server)
4. Login → New token saved ✓

### Scenario 2: CRUD Operations

1. Login → Token saved ✓
2. Create Post → Slug saved automatically ✓
3. Get Single Post → Uses saved slug ✓
4. Update Post → Uses saved slug ✓
5. Delete Post → Uses saved slug ✓

### Scenario 3: Filtering & Sorting

1. Get All Posts (default)
2. Search Posts (keyword: "Laravel")
3. Filter by Author (your UUID)
4. Sort by Title (ascending)
5. Pagination (per_page=20, page=2)

### Scenario 4: Authorization Testing

1. Login as User A
2. Create Post as User A → Slug saved ✓
3. Login as User B → New token saved ✓
4. Try to Update User A's Post → 403 Forbidden ✓
5. Try to Delete User A's Post → 403 Forbidden ✓

## 🔍 Viewing Auto-Saved Variables

1. Click **Console** at bottom of Postman (Ctrl+Alt+C)
2. Send Register or Login request
3. See console output: `Token saved: 1|xxxxx...`

Or check environment:

1. Click **Environments** in left sidebar
2. Select **"Foogu CMS API - Local"**
3. See `auth_token` and `post_slug` current values

## 💡 Tips

1. **Token Not Working?**

    - Check Console for "Token saved" message
    - Verify environment is selected (top right)
    - Manually copy token from response if needed

2. **Post Slug Not Found?**

    - Create a post first
    - Check Console for "Post slug saved" message
    - Or manually set `{{post_slug}}` in environment

3. **Change Base URL?**

    - Edit environment variable `base_url`
    - Example: `http://127.0.0.1:8000` or your production URL

4. **Testing in Production?**
    - Duplicate environment
    - Rename to "Foogu CMS API - Production"
    - Change `base_url` to production URL

## 🛠️ Customization

### Add Pre-request Scripts

For random data generation:

```javascript
// In Pre-request Script tab
pm.environment.set(
    "random_email",
    pm.variables.replaceIn("user{{$timestamp}}@example.com")
);
pm.environment.set(
    "random_name",
    pm.variables.replaceIn("User {{$randomInt}}")
);
```

### Add More Test Scripts

For auto-saving author_id:

```javascript
// In Tests tab of Login/Register
if (pm.response.code === 200 || pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.user && jsonData.user.id) {
        pm.environment.set("author_id", jsonData.user.id);
    }
}
```

## 📚 Additional Resources

-   API Documentation: `API_DOCUMENTATION.md`
-   Laravel Docs: https://laravel.com/docs
-   Postman Docs: https://learning.postman.com/

## ✅ Checklist

Before starting:

-   [ ] Laravel server running (`php artisan serve`)
-   [ ] Database migrated (`php artisan migrate`)
-   [ ] Collection imported
-   [ ] Environment imported and selected
-   [ ] `base_url` matches your server

Ready to test:

-   [ ] Register creates account (token saved ✓)
-   [ ] Login works (token saved ✓)
-   [ ] Create post works (slug saved ✓)
-   [ ] Get single post by slug works
-   [ ] Update post by slug works (only author)
-   [ ] Delete post by slug works (only author)
-   [ ] Search, filter, and sort work
-   [ ] Logout revokes token

---

**Happy Testing! 🚀**

Need help? Check the API documentation or create an issue.
