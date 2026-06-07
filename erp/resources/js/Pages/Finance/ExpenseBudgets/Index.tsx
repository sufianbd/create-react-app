import React from 'react';

interface ExpenseBudget {
    id: number;
    department: string;
    period: string;
    allocated_amount: string;
    spent_amount: string;
    currency: string;
    status: string;
    category: string | null;
    budget_code: string | null;
}

interface Props {
    expenseBudgets: {
        data: ExpenseBudget[];
        current_page: number;
        last_page: number;
    };
    filters: {
        department?: string;
        status?: string;
    };
}

export default function Index({ expenseBudgets, filters }: Props) {
    return (
        <div>
            <h1>Expense Budgets</h1>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Period</th>
                        <th>Allocated</th>
                        <th>Spent</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    {expenseBudgets.data.map((budget) => (
                        <tr key={budget.id}>
                            <td>{budget.budget_code}</td>
                            <td>{budget.department}</td>
                            <td>{budget.period}</td>
                            <td>{budget.allocated_amount}</td>
                            <td>{budget.spent_amount}</td>
                            <td>{budget.status}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
