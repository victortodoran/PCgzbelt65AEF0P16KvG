# Subscription Management System (Requirements)
You are tasked with building a subscription management system that allows users to subscribe to a service and manage their subscription status. The system will consist of a REST API backend and a simple frontend built using Vue.js.

## Backend
The backend should be built using PHP 8 and Symfony 5.4 (or higher) and should expose the following API endpoints:

### Authentication
- `POST /api/auth/login`: Authenticate a user and retrieve an access token.
- `POST /api/auth/register`: Register a new user.

### Subscriptions
- `GET /api/subscriptions`: Get a list of all the available subscriptions.
- `GET /api/subscriptions/{subscription_id}`: Get a single subscription by ID.
- `POST /api/subscriptions/{subscription_id}/subscribe`: Subscribe to a subscription. Subscribing to a subscription cancels an already existing subscription.
- `POST /api/subscriptions/{subscription_id}/unsubscribe`: Unsubscribe from a subscription.
- `GET /api/subscriptions/me`: Get the current user's subscriptions.

### Payment
- `POST /api/payment`: Make a payment to subscribe to a subscription.

The API should use Doctrine to interact with a MySQL database. The database should have the following schema:

```
users
- id: int
- name: string
- email: string
- password: string

subscriptions
- id: int
- name: string
- description: string
- price: float
- duration: int

user_subscriptions
- user_id: int
- subscription_id: int
- status: string
- start_date: datetime
- end_date: datetime
```

The API should follow REST principles and should return JSON responses.