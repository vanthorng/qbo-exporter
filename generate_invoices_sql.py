import random
import uuid
from datetime import datetime, timedelta

# Number of invoices you want
TOTAL = 1_000_000

# Output file
SQL_FILE = "invoices_data.sql"

# Invoice table name
TABLE_INVOICE = "invoices"
TABLE_ITEMS = "invoice_items"


def random_date(start, end):
    """Return random datetime between two dates."""
    return start + timedelta(
        seconds=random.randint(0, int((end - start).total_seconds()))
    )


def generate_invoice_record(i):
    invoice_number = f"INV-{i:07d}"   # no duplicates guaranteed
    customer_id = random.randint(1, 5000)
    issue_date = random_date(datetime(2023, 1, 1), datetime(2025, 12, 31))
    due_date = issue_date + timedelta(days=random.randint(5, 30))
    status = random.choice(["draft", "sent", "paid", "overdue"])
    currency = random.choice(["USD", "EUR", "GBP", "KHR"])
    subtotal = round(random.uniform(20, 5000), 2)
    discount_total = round(random.uniform(0, 100), 2)
    tax_total = round(subtotal * 0.1, 2)
    total = round(subtotal - discount_total + tax_total, 2)
    paid_total = total if status == "paid" else 0
    balance_due = total - paid_total

    # SQL INSERT for invoice
    sql = (
        f"INSERT INTO `{TABLE_INVOICE}` "
        f"(invoice_number, customer_id, issue_date, due_date, status, currency, "
        f"subtotal, discount_total, tax_total, total, paid_total, balance_due, created_at, updated_at) "
        f"VALUES ('{invoice_number}', {customer_id}, '{issue_date.date()}', "
        f"'{due_date.date()}', '{status}', '{currency}', "
        f"{subtotal}, {discount_total}, {tax_total}, {total}, {paid_total}, "
        f"{balance_due}, NOW(), NOW());\n"
    )

    return sql


def generate_item_records(invoice_id):
    """Generate 1–5 invoice item records."""
    item_sql = ""
    for _ in range(random.randint(1, 5)):
        name = random.choice(["Service A", "Service B", "Product X", "Subscription", "Item Z"])
        description = f"{name} description"
        quantity = round(random.uniform(1, 10), 2)
        unit_price = round(random.uniform(10, 200), 2)
        discount_amount = round(random.uniform(0, 5), 2)
        discount_percent = 0
        tax_rate = 10
        line_total = round((quantity * unit_price) - discount_amount + ((quantity * unit_price) * 0.1), 2)

        item_sql += (
            f"INSERT INTO `{TABLE_ITEMS}` "
            f"(invoice_id, name, description, quantity, unit, unit_price, discount_amount, "
            f"discount_percent, tax_rate, line_total, sort_order, created_at, updated_at) "
            f"VALUES ({invoice_id}, '{name}', '{description}', {quantity}, 'unit', "
            f"{unit_price}, {discount_amount}, {discount_percent}, {tax_rate}, {line_total}, "
            f"0, NOW(), NOW());\n"
        )

    return item_sql


# ---------------------------
# Generate the SQL file
# ---------------------------

print("Generating SQL file... This may take several minutes.")

with open(SQL_FILE, "w", encoding="utf-8") as f:
    for i in range(1, TOTAL + 1):
        # Invoice
        f.write(generate_invoice_record(i))

        # Invoice Items
        f.write(generate_item_records(i))

        if i % 10_000 == 0:
            print(f"Generated: {i:,} invoices...")

print(f"\nDONE! File created: {SQL_FILE}")
