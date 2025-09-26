# 🚀 Vercel Serverless 배포 가이드

## 📂 프로젝트 구조
```
skin4/
├── api/
│   └── orders.js          # Vercel 서버리스 함수
├── index.html             # 메인 대시보드
├── vercel.json           # Vercel 설정
├── .env.local            # 로컬 환경변수
└── package.json          # 의존성 (필요시)
```

## 🚀 빠른 배포 (3분)

### 1단계: Vercel 계정 준비
- [vercel.com](https://vercel.com) 회원가입/로그인
- GitHub 연결 (권장) 또는 CLI 사용

### 2단계: 프로젝트 업로드

#### GitHub 연결 방식 (권장):
1. GitHub에 저장소 생성
2. 파일들 커밋 & 푸시
3. Vercel에서 GitHub 저장소 import

#### CLI 방식 (빠름):
```bash
npx vercel
# 프롬프트 따라하기:
# - Set up and deploy? Y
# - Which scope? (계정 선택)
# - Link to existing project? N
# - What's your project's name? cafe24-dashboard
# - In which directory? ./
# - Override settings? N
```

### 3단계: 환경변수 설정
Vercel Dashboard → Project → Settings → Environment Variables

**필수 변수들:**
```
CAFE24_ACCESS_TOKEN = kTc2TEfIECcCzeExHh4gLA
CAFE24_REFRESH_TOKEN = Bci3OoS9ER5pe2BVqnYKpA
CAFE24_CLIENT_ID = cW116Up5MxYlXrTlmzCjgA
CAFE24_MALL_ID = udit1
CAFE24_SHOP_NO = 1
```

**⚠️ 중요**: Environment 타입을 **Production, Preview, Development** 모두 체크!

### 4단계: 재배포
환경변수 설정 후 반드시 재배포:
- Dashboard → Deployments → 최신 배포 → "Redeploy" 버튼

## 🔗 접속 URL

배포 완료 후:
```
메인 대시보드: https://your-project.vercel.app
API 테스트: https://your-project.vercel.app/api/orders?action=count
```

## 🧪 테스트 방법

### 1. API 직접 테스트
```bash
curl "https://your-project.vercel.app/api/orders?action=count&start_date=2025-09-26&end_date=2025-09-26"
```

### 2. 대시보드 테스트
브라우저에서 메인 URL 접속 후:
- "🔄 전체 새로고침" 버튼
- "🧪 단일 API 테스트" 버튼

### 3. 다양한 엔드포인트
```
주문 수 조회: /api/orders?action=count&start_date=2025-09-26
상품 목록: /api/orders?action=products&limit=5
토큰 갱신: /api/orders?action=refresh
대시보드 데이터: /api/orders?action=dashboard
```

## 🔧 트러블슈팅

### 환경변수 오류
```json
{"error": "환경변수가 설정되지 않았습니다"}
```
**해결**: Vercel Dashboard에서 환경변수 재확인 및 재배포

### API 호출 실패
```json
{"error": "서버 오류", "message": "Cafe24 API Error: 401"}
```
**해결**: ACCESS_TOKEN 갱신 또는 REFRESH_TOKEN으로 토큰 갱신

### 함수 타임아웃
**원인**: 서버리스 함수는 10초 제한 (Pro는 60초)
**해결**: API 호출 최적화 또는 Pro 플랜 업그레이드

## 📊 Vercel 서버리스 장점

### 1. 자동 스케일링
- 트래픽에 따라 자동으로 인스턴스 증감
- 사용하지 않을 때는 0으로 축소

### 2. 글로벌 CDN
- 전 세계 엣지 로케이션에서 서빙
- 빠른 응답 시간

### 3. 제로 서버 관리
- 서버 설정, 유지보수, 업데이트 불필요
- 코드에만 집중

### 4. 비용 효율성
```
무료 플랜:
- 100GB 대역폭/월
- 100회 서버리스 함수 실행/일
- 무제한 정적 사이트

Pro 플랜 ($20/월):
- 1TB 대역폭/월
- 1,000회 함수 실행/일
- 60초 함수 타임아웃
```

## 🔒 보안 설정

### 1. 환경변수 보호
- API 키는 절대로 코드에 하드코딩 금지
- .env.local은 .gitignore에 추가

### 2. CORS 설정
현재 설정은 모든 도메인 허용 (`*`)
프로덕션에서는 특정 도메인으로 제한:
```javascript
res.setHeader('Access-Control-Allow-Origin', 'https://yourdomain.com');
```

### 3. Rate Limiting
많은 요청으로부터 보호하려면 Vercel Edge Config 사용 권장

## 📈 성능 모니터링

### Vercel Dashboard
- Function 실행 통계
- 에러 로그
- 응답 시간

### 로그 확인
```bash
vercel logs your-project-name
```

## 🚀 완료!

이제 다음 URL들이 작동합니다:

1. **메인 대시보드**: `https://your-project.vercel.app`
2. **API 엔드포인트**: `https://your-project.vercel.app/api/orders`

**✅ 자동 스케일링**
**✅ 전 세계 CDN**
**✅ 제로 다운타임**
**✅ 안전한 API 키 관리**

마감 시간 내에 완벽한 서버리스 솔루션 완성! 🎉