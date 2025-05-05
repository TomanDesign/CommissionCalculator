# 💳 Commission Calculator

A PHP CLI tool that calculates transaction commissions based on BIN (Bank Identification Number) and currency exchange rates. The project uses clean architecture principles, including interfaces, dependency injection, and PHPUnit-based unit testing.

---

## 🔧 Requirements

- PHP 8.2 or higher
- Composer
- Internet access (to use external APIs)
- Git (for cloning)

---

## 📦 Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/YourUsername/CommissionCalculator.git
cd CommissionCalculator
composer install
```

---

## 🚀 Usage

Prepare a file named `input.txt` with one JSON object per line, for example:

```
{"bin":"45717360","amount":"100.00","currency":"EUR"}
{"bin":"516793","amount":"50.00","currency":"USD"}
{"bin":"45417360","amount":"10000.00","currency":"JPY"}
{"bin":"41417360","amount":"130.00","currency":"USD"}
{"bin":"4745030","amount":"2000.00","currency":"GBP"}
```

Run the calculator:

```bash
php app.php input.txt
```

You will receive output like:

```
1.0000000000
0.5167882931
1.5703458932
2.4034587239
39.9988371927
```

Each line corresponds to the commission for the respective transaction.

---

## 🧪 Running Tests

Tests are written with PHPUnit and use mock objects to avoid external API calls.

Run all tests with:

```bash
vendor/bin/phpunit
```

Or via Composer:

```bash
composer test
```

You’ll find tests in:

```
tests/CommissionCalculatorTest.php
```

---

## ✅ Design Principles Used

- 🔁 **Interfaces** – Allow swapping different implementations (API, cache, mock)
- 💉 **Dependency Injection** – Improves testability and flexibility
- 🧪 **Mockable architecture** – No real API calls during tests
- ♻️ **PSR-4 autoloading** – Clean code structure with Composer

---

## 📄 License

MIT © [Your Name or Organization]

---

## 🤝 Contributing

Feel free to fork, improve, or extend this tool. Pull requests are welcome.

---

## 🌍 API Services Used

- [Binlist](https://binlist.net/) – for BIN to country resolution
- [ExchangeRate.host](https://exchangerate.host/) – for up-to-date currency conversion rates
