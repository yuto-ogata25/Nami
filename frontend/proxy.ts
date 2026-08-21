import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

// バックエンド(config/session.php)のセッションCookie名。APP_NAME を変更した場合はここも合わせる。
const SESSION_COOKIE = "laravel-session";

// UX目的の簡易チェック（セッションCookieの有無のみ）。
// 実際の認可はLaravel側の auth:web / auth:operator ミドルウェアが最終防衛線であり、ここでは代替できない。
export function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const hasSession = request.cookies.has(SESSION_COOKIE);

  if (pathname.startsWith("/operator") && pathname !== "/operator/login") {
    if (!hasSession) {
      return NextResponse.redirect(new URL("/operator/login", request.url));
    }
  }

  if (pathname.startsWith("/strategy-goals")) {
    if (!hasSession) {
      return NextResponse.redirect(new URL("/login", request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/operator/:path*", "/strategy-goals/:path*"],
};
