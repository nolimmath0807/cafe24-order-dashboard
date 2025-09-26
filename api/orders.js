// api/orders.js - Vercel 서버리스 함수
export default async function handler(req, res) {
  // CORS 헤더 설정
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    res.status(200).end();
    return;
  }

  try {
    // 환경변수에서 설정값 가져오기
    const ACCESS_TOKEN = process.env.CAFE24_ACCESS_TOKEN;
    const REFRESH_TOKEN = process.env.CAFE24_REFRESH_TOKEN;
    const CLIENT_ID = process.env.CAFE24_CLIENT_ID;
    const MALL_ID = process.env.CAFE24_MALL_ID;
    const SHOP_NO = process.env.CAFE24_SHOP_NO || '1';

    if (!ACCESS_TOKEN || !MALL_ID) {
      return res.status(500).json({
        error: '환경변수가 설정되지 않았습니다',
        missing: !ACCESS_TOKEN ? 'ACCESS_TOKEN' : 'MALL_ID'
      });
    }

    // 쿼리 파라미터
    const { action, start_date, end_date, limit = 10 } = req.query;

    if (action === 'refresh') {
      // 토큰 갱신
      const response = await fetch(`https://${MALL_ID}.cafe24api.com/api/v2/oauth/token`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Authorization': `Basic ${Buffer.from(CLIENT_ID + ':').toString('base64')}`
        },
        body: new URLSearchParams({
          grant_type: 'refresh_token',
          client_id: CLIENT_ID,
          refresh_token: REFRESH_TOKEN
        })
      });

      const data = await response.json();
      return res.json(data);
    }

    if (action === 'count' || !action) {
      // 주문 수 조회
      const startDate = start_date || new Date().toISOString().split('T')[0];
      const endDate = end_date || startDate;

      const url = `https://${MALL_ID}.cafe24api.com/api/v2/admin/orders/count?` +
        new URLSearchParams({
          shop_no: SHOP_NO,
          start_date: startDate,
          end_date: endDate
        });

      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${ACCESS_TOKEN}`,
          'Content-Type': 'application/json',
          'X-Cafe24-Api-Version': '2025-09-01'
        }
      });

      if (!response.ok) {
        throw new Error(`Cafe24 API Error: ${response.status}`);
      }

      const data = await response.json();
      return res.json(data);
    }

    if (action === 'products') {
      // 상품 목록 조회
      const url = `https://${MALL_ID}.cafe24api.com/api/v2/admin/products?` +
        new URLSearchParams({
          shop_no: SHOP_NO,
          limit: limit.toString()
        });

      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${ACCESS_TOKEN}`,
          'Content-Type': 'application/json',
          'X-Cafe24-Api-Version': '2025-09-01'
        }
      });

      const data = await response.json();
      return res.json(data);
    }

    // 대시보드용 통합 데이터
    if (action === 'dashboard') {
      const today = new Date().toISOString().split('T')[0];
      const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
      const weekAgo = new Date(Date.now() - 7 * 86400000).toISOString().split('T')[0];
      const monthStart = new Date().toISOString().slice(0, 8) + '01';

      const results = {
        last_updated: new Date().toISOString(),
        data: {}
      };

      const queries = [
        { key: 'today', start: today, end: today },
        { key: 'yesterday', start: yesterday, end: yesterday },
        { key: 'week', start: weekAgo, end: today },
        { key: 'month', start: monthStart, end: today }
      ];

      for (const query of queries) {
        try {
          const url = `https://${MALL_ID}.cafe24api.com/api/v2/admin/orders/count?` +
            new URLSearchParams({
              shop_no: SHOP_NO,
              start_date: query.start,
              end_date: query.end
            });

          const response = await fetch(url, {
            headers: {
              'Authorization': `Bearer ${ACCESS_TOKEN}`,
              'Content-Type': 'application/json',
              'X-Cafe24-Api-Version': '2025-09-01'
            }
          });

          const data = await response.json();
          results.data[query.key] = {
            count: data.count || 0,
            period: `${query.start} ~ ${query.end}`
          };

          // API 호출 간격
          await new Promise(resolve => setTimeout(resolve, 200));

        } catch (error) {
          results.data[query.key] = {
            error: error.message,
            period: `${query.start} ~ ${query.end}`
          };
        }
      }

      return res.json(results);
    }

    // 잘못된 액션
    res.status(400).json({
      error: '지원하지 않는 액션',
      available_actions: ['count', 'products', 'refresh', 'dashboard']
    });

  } catch (error) {
    console.error('API Error:', error);
    res.status(500).json({
      error: '서버 오류',
      message: error.message
    });
  }
}