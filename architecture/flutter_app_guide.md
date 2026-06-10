# Flutter Mobile App Development Guide (Laravel Backend)

This guide details the mobile architecture, visual systems, state management flows, and API integrations for the **HumaNode HRMS Flutter App** communicating with the **Laravel API Backend**.

---

## 📱 Mobile Visual System & Theme

The Flutter app leverages **Material 3** with custom component styling to match the modern, premium glassmorphic UI of the Web App.

*   **Themes:** Dark Mode first, with a clean Light Mode alternative.
*   **Aesthetic Accents:**
    *   Subtle gradients on primary buttons using `BoxDecoration`.
    *   Glassmorphic effect cards using `BackdropFilter` or translucent container borders.
    *   Custom micro-animations (e.g., pulsing clock-in button, slide-in list views, `Hero` transitions for user profile photos).

### Material 3 Custom Theme Setup
```dart
final darkThemeData = ThemeData(
  useMaterial3: true,
  brightness: Brightness.dark,
  colorScheme: const ColorScheme.dark(
    primary: Color(0xFF00E5FF),      // Cyber Cyan
    secondary: Color(0xFF8A2BE2),    // Neon Purple/Indigo
    background: Color(0xFF0F172A),   // Slate 900
    surface: Color(0xFF1E293B),      // Slate 800
    onPrimary: Color(0xFF0F172A),
    onBackground: Color(0xFFF8FAFC),
  ),
);
```

---

## 📂 Project Directory Structure (Feature-First)

```text
mobile_app/
├── lib/
│   ├── core/
│   │   ├── constants/             # API urls (points to Laravel host), themes
│   │   ├── network/               # dio_client.dart (HTTP configurations)
│   │   ├── routes/                # GoRouter mappings
│   │   └── utils/                 # Geofence math, date helpers
│   ├── features/
│   │   ├── auth/                  # LoginScreen, Biometrics handler
│   │   ├── dashboard/             # Announcements dashboard widget
│   │   ├── attendance/            # Geofenced ClockInButton, GPS trigger
│   │   ├── leaves/                # LeaveBalanceGrid, ApplyLeaveBottomSheet
│   │   └── manager/               # Manager team review screens
│   └── main.dart
└── pubspec.yaml
```

---

## 🔌 API Client (Dio + Riverpod)

To communicate with the Laravel REST API, configure **Dio** and manage state using **Riverpod**.

### 1. Dio Client Setup (`lib/core/network/dio_client.dart`)
```dart
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class DioClient {
  final Dio _dio = Dio(BaseOptions(
    // Use 10.0.2.2 for Android Emulator, localhost for iOS simulator
    baseUrl: 'http://10.0.2.2:8000/api', 
    connectTimeout: const Duration(seconds: 10),
    receiveTimeout: const Duration(seconds: 10),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  ));

  DioClient() {
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString('auth_token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (DioException e, handler) {
        if (e.response?.statusCode == 401) {
          // Trigger redirect to login view
        }
        return handler.next(e);
      },
    ));
  }

  Dio get dio => _dio;
}
```

---

## 🛠️ Pubspec Dependencies & Setup

Configure your `pubspec.yaml` with the following packages for vibe-coding integration:

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # State Management & Routing
  flutter_riverpod: ^2.5.0
  go_router: ^14.0.0

  # API Integration
  dio: ^5.5.0                    # Robust REST API Client replacing Supabase
  shared_preferences: ^2.2.3      # Storing local JWT tokens

  # Hardware Access & Biometrics
  geolocator: ^12.0.0            # GPS lookup for attendance
  local_auth: ^2.2.0             # Fingerprint/FaceID login
  
  # UI & Utilities
  lucide_icons: ^0.300.0         # Modern icons
  intl: ^0.19.0                  # Date parsing
  syncfusion_flutter_pdfviewer: ^25.1.0 # Displaying payslips directly
```

---

## ⚡ Core Mobile Features & Laravel Endpoints

Map your Flutter widgets to these Laravel API calls:

### 1. Authentication (Login)
*   **API Target:** `POST /api/login` (Body: `email`, `password`)
*   **Action:** If successful, saving `token` inside `SharedPreferences` triggers Riverpod to navigate to the Home screen.

### 2. Geofenced Clock-In (`features/attendance/`)
*   **Logic:** Once coordinates are calculated and validated (within 100 meters of the office), trigger the API call:
    ```dart
    Future<void> submitClockIn(double lat, double lng, bool isField) async {
      await dioClient.dio.post('/attendance/clock-in', data: {
        'location_lat': lat,
        'location_lng': lng,
        'is_field_checkin': isField,
      });
    }
    ```

### 3. Leave Requests (`features/leaves/`)
*   **Endpoints:**
    *   `GET /api/leaves/balances`: Retrieves remaining days for each Leave Policy.
    *   `POST /api/leaves/apply`: Submits a new leave application (Params: `leave_policy_id`, `start_date`, `end_date`, `reason`).

### 4. Manager Self-Service (`features/manager/`)
*   **Endpoints:**
    *   `GET /api/manager/team-attendance`: Fetches list of team members logged in today.
    *   `PATCH /api/manager/leaves/{id}/approve`: Approves (status `Approved`) or rejects a leave request.
