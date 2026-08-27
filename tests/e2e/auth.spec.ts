import { expect, test, type Page, type TestInfo } from '@playwright/test';

async function attachPageScreenshot(
  page: Page,
  testInfo: TestInfo,
  name: string,
) {
  await testInfo.attach(name, {
    body: await page.screenshot({ fullPage: true }),
    contentType: 'image/png',
  });
}

test.describe('Xác thực Pawora', () => {
  test('khách hàng đăng nhập thành công', async ({ page }, testInfo) => {
    await page.goto('dang-nhap');

    await page.getByLabel('Email', { exact: true }).fill('khach@pawora.vn');
    await page.getByLabel('Mật khẩu', { exact: true }).fill('Pawora@2026');
    await page.getByRole('button', { name: 'Đăng nhập', exact: true }).click();

    await expect(page).toHaveURL(/\/PetShopnew\/public\/?$/);
    await expect(page.locator('[data-toast]')).toContainText(
      'Chào mừng bạn trở lại Pawora.',
    );
    await expect(
      page.getByRole('link', { name: /Mở tài khoản của Nguyễn An/ }),
    ).toBeVisible();

    await attachPageScreenshot(page, testInfo, 'Đăng nhập thành công');
  });

  test('khách hàng nhập sai mật khẩu và nhận thông báo lỗi', async ({
    page,
  }, testInfo) => {
    await page.goto('dang-nhap');

    await page.getByLabel('Email', { exact: true }).fill('khach@pawora.vn');
    await page.getByLabel('Mật khẩu', { exact: true }).fill('SaiMatKhau123');
    await page.getByRole('button', { name: 'Đăng nhập', exact: true }).click();

    await expect(page).toHaveURL(/\/PetShopnew\/public\/dang-nhap$/);
    await expect(page.locator('#login-error-summary')).toContainText(
      'Email hoặc mật khẩu chưa đúng.',
    );
    await expect(page.getByLabel('Email', { exact: true })).toHaveValue(
      'khach@pawora.vn',
    );

    await attachPageScreenshot(page, testInfo, 'Đăng nhập sai mật khẩu');
  });

  test('biểu mẫu đăng nhập chặn dữ liệu bắt buộc bị bỏ trống', async ({
    page,
  }, testInfo) => {
    await page.goto('dang-nhap');

    await page.getByRole('button', { name: 'Đăng nhập', exact: true }).click();

    const email = page.getByLabel('Email', { exact: true });
    await expect(page).toHaveURL(/\/PetShopnew\/public\/dang-nhap$/);
    await expect(email).toBeFocused();
    await expect
      .poll(() =>
        email.evaluate((input: HTMLInputElement) => input.validationMessage),
      )
      .not.toBe('');

    await attachPageScreenshot(page, testInfo, 'Đăng nhập thiếu dữ liệu');
  });

  test('khách mới đăng ký tài khoản thành công', async ({ page }, testInfo) => {
    const uniqueEmail = `playwright.${Date.now()}@example.com`;

    await page.goto('dang-ky');

    await page.getByLabel('Họ và tên', { exact: true }).fill('Khách Playwright');
    await page.getByLabel('Email', { exact: true }).fill(uniqueEmail);
    await page.getByLabel('Số điện thoại', { exact: true }).fill('0909123456');
    await page.getByLabel('Mật khẩu', { exact: true }).fill('Playwright@2026');
    await page
      .getByLabel('Nhập lại mật khẩu', { exact: true })
      .fill('Playwright@2026');
    await page
      .getByRole('button', { name: 'Tạo tài khoản', exact: true })
      .click();

    await expect(page).toHaveURL(/\/PetShopnew\/public\/?$/);
    await expect(page.locator('[data-toast]')).toContainText(
      'Tài khoản Pawora của bạn đã sẵn sàng.',
    );
    await expect(
      page.getByRole('link', { name: 'Mở tài khoản của Khách Playwright' }),
    ).toBeVisible();

    await attachPageScreenshot(page, testInfo, 'Đăng ký thành công');
  });

  test('đăng ký từ chối email đã tồn tại', async ({ page }, testInfo) => {
    await page.goto('dang-ky');

    await page.getByLabel('Họ và tên', { exact: true }).fill('Khách Trùng Email');
    await page.getByLabel('Email', { exact: true }).fill('khach@pawora.vn');
    await page.getByLabel('Số điện thoại', { exact: true }).fill('0909123456');
    await page.getByLabel('Mật khẩu', { exact: true }).fill('Playwright@2026');
    await page
      .getByLabel('Nhập lại mật khẩu', { exact: true })
      .fill('Playwright@2026');
    await page
      .getByRole('button', { name: 'Tạo tài khoản', exact: true })
      .click();

    await expect(page).toHaveURL(/\/PetShopnew\/public\/dang-ky$/);
    await expect(page.getByRole('alert')).toBeVisible();
    await expect(page.getByRole('alert')).toContainText(/email/i);
    await expect(page.getByLabel('Email', { exact: true })).toHaveValue(
      'khach@pawora.vn',
    );

    await attachPageScreenshot(page, testInfo, 'Đăng ký trùng email');
  });

  test('đăng ký từ chối mật khẩu xác nhận không khớp', async ({
    page,
  }, testInfo) => {
    await page.goto('dang-ky');

    await page.getByLabel('Họ và tên', { exact: true }).fill('Khách Mật Khẩu');
    await page
      .getByLabel('Email', { exact: true })
      .fill(`mismatch.${Date.now()}@example.com`);
    await page.getByLabel('Số điện thoại', { exact: true }).fill('0909123456');
    await page.getByLabel('Mật khẩu', { exact: true }).fill('Playwright@2026');
    await page
      .getByLabel('Nhập lại mật khẩu', { exact: true })
      .fill('Playwright@9999');
    await page
      .getByRole('button', { name: 'Tạo tài khoản', exact: true })
      .click();

    await expect(page).toHaveURL(/\/PetShopnew\/public\/dang-ky$/);
    await expect(page.getByRole('alert')).toBeVisible();
    await expect(page.getByRole('alert')).toContainText(/password|mật khẩu/i);

    await attachPageScreenshot(page, testInfo, 'Đăng ký sai xác nhận mật khẩu');
  });
});
