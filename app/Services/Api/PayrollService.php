<?php 
namespace App\Services\Api;

use App\Models\Payroll;
use App\Models\ScoreCard;
 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayrollApprovedMail;
use App\Mail\SalaryMismatchMail;
use App\Models\Apartment;
use App\Models\ApprovalHistory;
use App\Models\Deduction;
use App\Models\EmployeeLoan;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\Sim;
use App\Models\User;

class PayrollService
{
    public function createPayroll(array $data)
    {
        return DB::transaction(function () use ($data) {
            $scoreCard = ScoreCard::with('employee')->findOrFail($data['scoreCardId']);

            if (Payroll::where('scorecardId', $scoreCard->id)->lockForUpdate()->exists()) {
                throw new \Exception('Payroll already exists for this scorecard.');
            }
            // 👇 HERE
            if ($scoreCard->ScoreCardStatus !== 'pending') {
                throw new \Exception('ScoreCard already processed.');
            }
            if (!$scoreCard->employee) {
                throw new \Exception('Employee not found for this scorecard.');
            }
            if($scoreCard->finalSalary <230 && $scoreCard->totalHoursWorked >=30){
                throw new \Exception('The final salary is below the minimum threshold of $230 for employees who worked more than 30 hours. Please review the scorecard data.');
            }

             $employee = $scoreCard->employee;
 

            if ($scoreCard->finalSalary <= 0) {
                throw new \Exception('Final salary cannot be below zero.');
            }

            // =======================
            // LOAN
            // =======================
            $employeeLoan = EmployeeLoan::where('employeeId', $employee->id)
                ->where('loanStatus', 'active')
                ->first();

            $loanAmountWithTax = 0;
            $loanDeduction = 0;

            if ($employeeLoan) {
                $loan = Loan::find($employeeLoan->loansId);
                if (!$loan) {
                throw new \Exception('Loan record not found.');
                 }

             
                $loanAmountWithTax = $loan->loanAmountWithTax;

                $loanDeduction = match ($loan->loanType) {
                    'phone' => 75,
                    'car' => 150,
                    default => 0
                };

                $loanDeduction = min(
                    $loanDeduction,
                    $loanAmountWithTax,
                    $employeeLoan->loanRentAmount
                );
                 
            }

            // =======================
            // DEDUCTIONS
            // =======================
            $apartmentDeduction = 0;
            $simDeduction = 0;

            if ($scoreCard->totalHoursWorked > 30) {
                $deduction = Deduction::where('employeeId', $employee->id)->first();

                if ($deduction) {
                    $apartment = Apartment::find($deduction->ApartmentId);
                    $sim = Sim::find($deduction->SimId);

                    $apartmentDeduction = $apartment?->ApartmentRent ?? 0;
                    $simDeduction = $sim?->simCardInstallment ?? 0;

                    $deductionReason = "Deductions applied for apartment and sim card.";
                } else {
                    $deductionReason = "No apartment or sim deductions.";
                }
            } else {
                $deductionReason = "No deductions (hours ≤ 30).";
            }

            $totalDeductions = $apartmentDeduction + $simDeduction;

            // =======================
            // FINAL SALARY CALCULATION
            // =======================
            $finalSalary = $scoreCard->finalSalary - $totalDeductions;

            if ($finalSalary <= $loanDeduction) {
                $loanDeduction = $finalSalary;
                $finalSalary = 0;
            } else {
                $finalSalary -= $loanDeduction; 
            }

            // =======================
            // UPDATE LOAN
            // =======================
            if ($employeeLoan) {
                $employeeLoan->loanRentAmount -= $loanDeduction;

                if ($employeeLoan->loanRentAmount <= 0) {
                    $employeeLoan->loanStatus = "completed";
                    $employeeLoan->loanRentAmount = 0;
                }

                $employeeLoan->save();
            }

            // =======================
            // CREATE PAYROLL (ONCE)
            // =======================
            $payroll = Payroll::create([
                'scorecardId' => $scoreCard->id,
                'loanAmount' => $loanAmountWithTax,
                'loanRentAmount' => $employeeLoan?->loanRentAmount ?? 0,
                'deductions' => $totalDeductions,
                'deductionReason' => $deductionReason,
                'finalSalary' => $finalSalary,
                'paymentStatus' => 'pending',
            ]);

            return [
                'payroll' => $payroll,
                'breakdown' => [
                    'loan_deduction' => $loanDeduction,
                    'remaining_loan' => $employeeLoan?->loanRentAmount ?? 0,
                    'apartment_deduction' => $apartmentDeduction,
                    'sim_deduction' => $simDeduction,
                    'deduction_reason' => $deductionReason,
                ],
                'summary' => [
                    'final_salary' => $finalSalary,
                ]
            ];
        });
    }
    // public function createPayroll(array $data)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Get the scorecard for the employee
    //         $scoreCard = ScoreCard::findOrFail($data['scoreCardId']);
    //         $employee = $scoreCard->employee;

    //         // Check if the final salary is below zero
    //         if ($scoreCard->finalSalary <= 0) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Final salary cannot be below zero. Please review the data.'
    //             ], 400); // Bad Request
    //         }

    //         // Fetch the loan from employeesloans (instead of directly from loans)
    //         $employeeLoan = \App\Models\EmployeeLoan::where('employeeId', $employee->id)
    //             ->where('loanStatus', 'active') // Ensure loan status is 'active'
    //             ->first();
            
    //         // Initialize loan related deductions
    //         $loanAmountWithTax = 0;
    //         $loanDeduction = 0;

    //         // Check if the employee has an active loan
    //         if ($employeeLoan) {
    //             $loan = \App\Models\Loan::find($employeeLoan->loansId);
    //             if ($loan) {
    //                 $loanAmountWithTax = $loan->loanAmountWithTax;

    //                 // Adjust loan based on loan type (phone or car)
    //                 if ($loan->loanType === 'phone') {
    //                     $loanDeduction = 75;
    //                 } elseif ($loan->loanType === 'car') {
    //                     $loanDeduction = 150;
    //                 }

    //                 // Ensure loan deduction does not exceed loan balance
    //                 if ($loanAmountWithTax < $loanDeduction) {
    //                     $loanDeduction = $loanAmountWithTax;
    //                 }
    //                 if ($employeeLoan->loanRentAmount < $loanDeduction) {
    //                     $loanDeduction = $employeeLoan->loanRentAmount;
    //                 }
    //             }
    //         }

    //         // Initialize apartment and sim values
    //         $apartmentDeduction = 0;
    //         $simDeduction = 0;

    //         // If totalHoursWorked <= 30, do not subtract apartment and sim
    //         if ($scoreCard->totalHoursWorked > 30) {
    //             // Deduct apartment rent and sim card installment if available
    //             $deduction = \App\Models\Deduction::where('employeeId', $employee->id)->first();
    //             if ($deduction) {
    //               $apartment = \App\Models\Apartment::find($deduction->ApartmentId);
    //               $sim = \App\Models\Sim::find($deduction->SimId);

    //               $apartmentDeduction = $apartment?->ApartmentRent ?? 0;
    //               $simDeduction = $sim?->simCardInstallment ?? 0;
    //               $deductionReason= "Deductions applied for apartment and sim card as total hours worked is greater than 30.";
    //             } else {
    //                 $apartmentDeduction = 0;
    //                 $simDeduction = 0;
    //                 $deductionReason = " He does not have sim card or apartment deductions.";
    //             }
    //         }else{
    //             $deductionReason = "No deductions applied for apartment and sim card as total hours worked is 30 or less.";
    //         }
  
    //         // Calculate the sum of deductions and loans
    //         $totalDeductions = $apartmentDeduction + $simDeduction;

    //         // If final salary is less than loanDeduction, subtract only the remaining balance
    //         if ($scoreCard->finalSalary <= $loanDeduction) {
                
    //             $remainingLoanDeduction = $loanDeduction - $scoreCard->finalSalary;
    //             $loanDeduction = $scoreCard->finalSalary;  // Subtract the remaining amount from loan
    //             if ($employeeLoan) {
    //                 $employeeLoan->loanRentAmount -= $loanDeduction;
    //                 if ($employeeLoan->loanRentAmount == 0) {
    //                     $employeeLoan->loanStatus = "completed";
    //                 }
    //                 // $emploans= $employeeLoan->loanRentAmount;
    //                 $employeeLoan->save();
    //             }

    //             // Update final salary after loan deduction
    //             $finalSalary = 0;  // Final salary is now 0 after deduction
    //             // Create the payroll record
    //             $payroll = \App\Models\Payroll::create([
    //                 'scorecardId' => $scoreCard->id,
    //                 'loanAmount' => $loanAmountWithTax,
    //                 'loanRentAmount' => $employeeLoan?->loanRentAmount ?? 0,
    //                 'deductions' => $apartmentDeduction + $simDeduction,
    //                 'deductionReason' => $deductionReason,
    //                 'finalSalary' => $finalSalary,
    //                 'paymentStatus' => 'pending', // Default status
    //             ]);

    //             DB::commit();
    //              // Return the payroll details with deductions breakdown
                
    //             return [
    //             'payroll' => $payroll,

    //             'breakdown' => [
    //                 'loan_deduction' => $loanDeduction,
    //                 'remaining_loan' => $employeeLoan?->loanRentAmount ?? 0,
    //                 'apartment_deduction' => $apartmentDeduction,
    //                 'sim_deduction' => $simDeduction,
    //                 'deduction_reason' => $deductionReason,
    //             ],

    //             'summary' => [
    //                 'final_salary' => $finalSalary,
    //             ]
    //         ];
    //         } else {
    //             $finalSalary = $scoreCard->finalSalary - $totalDeductions;

    //             if ($finalSalary <= $loanDeduction) {
    //                 $loanDeduction = $finalSalary;
    //                 if ($employeeLoan) {
    //                     $employeeLoan->loanRentAmount -= $loanDeduction;
    //                     if ($employeeLoan->loanRentAmount == 0) {
    //                         $employeeLoan->loanStatus = "completed";
    //                     }
    //                     // $emploans= $employeeLoan->loanRentAmount;
                            
    //                     $employeeLoan->save();
    //                 }

    //                 // Update final salary after loan deduction
    //                 $finalSalary = 0;
    //                 // Create the payroll record
    //                 $payroll = \App\Models\Payroll::create([
    //                     'scorecardId' => $scoreCard->id,
    //                     'loanAmount' => $loanAmountWithTax,
    //                     'loanRentAmount' => $employeeLoan?->loanRentAmount ?? 0,
    //                     'deductions' => $apartmentDeduction + $simDeduction,
    //                     'deductionReason' => $deductionReason,

    //                     'finalSalary' => $finalSalary,
    //                     'paymentStatus' => 'pending', // Default status
    //                 ]);
    //                 DB::commit();
    //                  // Return the payroll details with deductions breakdown
    //               return [
    //                 'payroll' => $payroll,

    //                 'breakdown' => [
    //                     'loan_deduction' => $loanDeduction,
    //                     'remaining_loan' => $employeeLoan?->loanRentAmount ?? 0,
    //                     'apartment_deduction' => $apartmentDeduction,
    //                     'sim_deduction' => $simDeduction,
    //                     'deduction_reason' => $deductionReason,
    //                 ],

    //                 'summary' => [
    //                     'final_salary' => $finalSalary,
    //                 ]
    //             ];
    //             }

    //             // Further loan deduction if any
    //             $finalSalary -= $loanDeduction;

    //             // Save remaining loan balance
    //             if ($employeeLoan) {
    //                 $employeeLoan->loanRentAmount -= $loanDeduction;
    //                 if ($employeeLoan->loanRentAmount == 0) {
    //                     $employeeLoan->loanStatus = "completed";
    //                 }
    //                 // $emploans= $employeeLoan->loanRentAmount;
    //                 $employeeLoan->save();
    //             }

    //             // Create the payroll record
    //             $payroll = \App\Models\Payroll::create([
    //                 'scorecardId' => $scoreCard->id,
    //                 'loanAmount' => $loanAmountWithTax,
    //                 'loanRentAmount' => $employeeLoan?->loanRentAmount ?? 0,
    //                 'deductions' => $apartmentDeduction + $simDeduction,
    //                 'deductionReason' => $deductionReason,
    //                 'finalSalary' => $finalSalary,
    //                 'paymentStatus' => 'pending', // Default status
    //             ]);

    //             DB::commit();
    //         }

    //         // Return the payroll details with deductions breakdown
    //           return [
    //             'payroll' => $payroll,

    //             'breakdown' => [
    //                 'loan_deduction' => $loanDeduction,
    //                 'remaining_loan' => $employeeLoan?->loanRentAmount ?? 0,
    //                 'apartment_deduction' => $apartmentDeduction,
    //                 'sim_deduction' => $simDeduction,
    //                 'deduction_reason' => $deductionReason,
    //             ],

    //             'summary' => [
    //                 'final_salary' => $finalSalary,
    //             ]
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw new \Exception('Payroll creation failed: ' . $e->getMessage());
    //     }
    // }
    // public function createPayroll(array $data)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Get the scorecard for the employee
    //         $scoreCard = ScoreCard::findOrFail($data['scoreCardId']);
    //         $employee = $scoreCard->employee;

    //         // Check if the final salary is below zero
    //         if ($scoreCard->finalSalary <= 0) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Final salary cannot be below zero. Please review the data.'
    //             ], 400); // Bad Request
    //         }

    //         // Fetch the loan from employeesloans (instead of directly from loans)
    //         $employeeLoan = \App\Models\EmployeeLoan::where('employeeId', $employee->id)
    //                                     ->where('loanStatus', 'active') // Ensure loan status is 'active'
    //                                     ->first();
                
                                        
    //         // Calculate total deductions from loans (if any)
    //         $loanAmountWithTax = 0;
    //         $loanDeduction = 0;
         
            
    //         if ($employeeLoan  ) {
    //             $loan = \App\Models\Loan::find($employeeLoan->loansId);
              
    //             if ($loan) {
                
    //                 $loanAmountWithTax = $loan->loanAmountWithTax;

    //                 // Adjust loan based on loan type (phone or car)
    //                 if ($loan->loanType === 'phone') {
    //                     $loanDeduction = 75;
    //                 } elseif ($loan->loanType === 'car') {
    //                     $loanDeduction = 150;
    //                 }

    //                 // Check if the loan amount is less than the deduction, and adjust
    //                 if ($loanAmountWithTax < $loanDeduction) {
    //                     $loanDeduction = $loanAmountWithTax; // Subtract only the remaining balance
    //                 }
    //                 if($employeeLoan->loanRentAmount < $loanDeduction){
    //                     $loanDeduction = $employeeLoan->loanRentAmount; // Subtract only the remaining balance
    //                 }

    //             }
    //         }
          

    //         // Initialize apartment and sim values
    //         $apartmentDeduction = 0;
    //         $simDeduction = 0;

    //         // If totalHoursWorked <= 30, do not subtract apartment and sim
    //         if ($scoreCard->totalHoursWorked > 30) {
    //             // Deduct apartment rent and sim card installment if available
    //             $deduction = \App\Models\Deduction::where('employeeId', $employee->id)->first();
    //            if ($deduction) {
    //                 $apartmentDeduction = \App\Models\Apartment::find($deduction->ApartmentId)->ApartmentRent;
    //                 $simDeduction = \App\Models\Sim::find($deduction->SimId)->simCardInstallment;
    //             } else {
    //                 $apartmentDeduction = 0;
    //                 $simDeduction = 0;
    //             }
    //         }
           

    //         // Calculate the sum of deductions and loans
    //         $totalDeductions = $apartmentDeduction + $simDeduction ;

            

    //         // If final salary is less than loanDeduction, subtract only the remaining balance
    //         if ($scoreCard->finalSalary <= $loanDeduction) {
    //             $remainingLoanDeduction = $loanDeduction - $scoreCard->finalSalary;
    //             $loanDeduction = $scoreCard->finalSalary;  // Subtract the remaining amount from loan
    //             $employeeLoan->loanRentAmount -= $loanDeduction;
    //               if($employeeLoan->loanRentAmount==0){
    //                 $employeeLoan->loanStatus ="completed";
    //             }
           
    //             $employeeLoan->save();

    //             // Update final salary after loan deduction
    //             $finalSalary = 0;  // Final salary is now 0 after deduction
    //              // Create the payroll record
    //             $payroll = \App\Models\Payroll::create([
    //                 'scorecardId' => $scoreCard->id,
    //                 'loanAmount' => $loanAmountWithTax,
    //                 'deductions' => $apartmentDeduction + $simDeduction,
    //                 'finalSalary' => $finalSalary,
    //                 'paymentStatus' => 'pending', // Default status
    //             ]);
    //         DB::commit();
      
    //         }else{
             
    //         $finalSalary = $scoreCard->finalSalary - $totalDeductions;
         
    //         if($finalSalary <= $loanDeduction){
            
    
    //             $loanDeduction = $finalSalary;  // Subtract the remaining amount from loan
    //             $employeeLoan->loanRentAmount -= $loanDeduction;
    //             if($employeeLoan->loanRentAmount==0){
    //                 $employeeLoan->loanStatus ="completed";
    //             }
      
    //             $employeeLoan->save();

    //             // Update final salary after loan deduction
    //             $finalSalary = 0;  // Final salary is now 0 after deduction
    //              // Create the payroll record
    //             $payroll = \App\Models\Payroll::create([
    //                 'scorecardId' => $scoreCard->id,
    //                 'loanAmount' => $loanAmountWithTax,
    //                 'deductions' => $apartmentDeduction + $simDeduction,
    //                 'finalSalary' => $finalSalary,
    //                 'paymentStatus' => 'pending', // Default status
    //             ]);
    //         DB::commit();
    //         // Return the payroll details with deductions breakdown
    //         return response()->json([
    //             'success' => true,
    //             'payroll' => $payroll,
    //             'loanDeduction' => $loanDeduction,
    //             'apartmentDeduction' => $apartmentDeduction,
    //             'simDeduction' => $simDeduction,
    //             'finalSalary' => $finalSalary,
    //             'message' => 'Payroll created successfully with deductions breakdown.'
    //         ]);
      
    //         }
    //         $finalSalary = $finalSalary - $loanDeduction;
            
    //         // Subtract from the loan amount in the employeeLoans table
    //         $employeeLoan->loanRentAmount -= $loanDeduction;
    //         if($employeeLoan->loanRentAmount==0){
    //                 $employeeLoan->loanStatus ="completed";
    //         }
    //         // Save the updated loan
    //         $employeeLoan->save();
    //         // Calculate the final salary after deductions

         

    //         // Create the payroll record
    //         $payroll = \App\Models\Payroll::create([
    //             'scorecardId' => $scoreCard->id,
    //             'loanAmount' => $loanAmountWithTax,
    //             'deductions' => $apartmentDeduction + $simDeduction,
    //             'finalSalary' => $finalSalary,
    //             'paymentStatus' => 'pending', // Default status
    //         ]);

    //         DB::commit();

    //         }
  

    //         // Return the payroll details with deductions breakdown
    //         return response()->json([
    //             'success' => true,
    //             'payroll' => $payroll,
    //             'loanDeduction' => $loanDeduction,
    //             'apartmentDeduction' => $apartmentDeduction,
    //             'simDeduction' => $simDeduction,
    //             'finalSalary' => $finalSalary,
    //             'message' => 'Payroll created successfully with deductions breakdown.'
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw new \Exception('Payroll creation failed: ' . $e->getMessage());
    //     }
    // }

       // Approve payroll by Third Shift Store Manager
   
    public function handleThirdShiftDecision(int $payrollId, string $action, ?string $comment): void
    {
        DB::transaction(function () use ($payrollId, $action, $comment) {

            $payroll = Payroll::findOrFail($payrollId);

            if ($payroll->paymentStatus !== 'pending') {
                throw new \Exception('Cannot process, payment is not pending.');
            }

            $exists = ApprovalHistory::where('payroll_id', $payrollId)
                ->where('role', 'third_shift')
                ->exists();

            if ($exists) {
                throw new \Exception('Already processed by third shift.');
            }

            ApprovalHistory::create([
                'payroll_id' => $payrollId,
                'approved_by' => auth()->id(),
                'role' => 'third_shift',
                'status' => $action === 'approve' ? 'approved' : 'rejected',
                'comment' => $comment,
                'approved_at' => now(),
            ]);

            // 🔥 if rejected → stop flow
            if ($action === 'reject') {
                $payroll->paymentStatus = 'failed';
                $payroll->save();
            }
        });
    }
 
    // Approve payroll by Senior Manager

    public function handleSeniorDecision(int $payrollId, string $action, ?string $comment): void
    {
        DB::transaction(function () use ($payrollId, $action, $comment) {

            $payroll = Payroll::with('scoreCard.employee')->findOrFail($payrollId);

            if ($payroll->paymentStatus !== 'pending') {
                throw new \Exception('Cannot process, payment is not pending.');
            }

            $thirdApproved = ApprovalHistory::where('payroll_id', $payrollId)
                ->where('role', 'third_shift')
                ->where('status', 'approved')
                ->exists();

            if (!$thirdApproved) {
                throw new \Exception('Third shift approval required first.');
            }

            $exists = ApprovalHistory::where('payroll_id', $payrollId)
                ->where('role', 'senior')
                ->exists();

            if ($exists) {
                throw new \Exception('Already processed by senior manager.');
            }

            ApprovalHistory::create([
                'payroll_id' => $payrollId,
                'approved_by' => auth()->id(),
                'role' => 'senior',
                'status' => $action === 'approve' ? 'approved' : 'rejected',
                'comment' => $comment,
                'approved_at' => now(),
            ]);

            if ($action === 'reject') {
                $payroll->paymentStatus = 'failed';
                $payroll->save();
                return;
            }

            // ✅ FINAL APPROVAL
            $payroll->paymentStatus = 'paid';
            $payroll->paymentDate = now();
            $payroll->save();

            if ($payroll->scoreCard) {
                $payroll->scoreCard->ScoreCardStatus = 'paid';
                $payroll->scoreCard->save();
            }
            //🔥 Send Notification + Email
            $employee = $payroll->scoreCard->employee ?? null;

            if ($employee && $employee->email) {

                $mailData = [
                    'hours' => $payroll->scoreCard->totalHoursWorked,
                    'deductions' => $payroll->deductions,
                    'loan' => $payroll->loanAmount,
                    'salary' => $payroll->finalSalary,
                ];

                // Save notification in DB
                Notification::create([
                    'employee_id' => $employee->id,
                    'type' => 'payroll_approved',
                    'message' => json_encode($mailData),
                ]);

                // Send email
                Mail::to($employee->email)->send(
                    new PayrollApprovedMail($mailData)
                );
            }
        });
    }
 
    public function getPayrolls(?int $storeId, array $filters)
    {
        $query = Payroll::query()
            ->select('payroll.*')
            ->whereHas('scoreCard.masterSchedule', function ($q) use ($storeId, $filters) {

                // ✅ Only apply store filter if exists
                if ($storeId !== null) {
                    $q->where('store_id', $storeId);
                }

                // Date filter
                if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                    $q->whereBetween('start_date', [
                        $filters['start_date'],
                        $filters['end_date']
                    ]);
                }
            });

        // Payment status filter
        if (!empty($filters['paymentStatus'])) {
            $query->where('paymentStatus', $filters['paymentStatus']);
        }

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }
    public function getPayrollById(?int $storeId, int $id)
    {
        return Payroll::with([
                'scoreCard.masterSchedule',
                'scoreCard.masterSchedule.schedules',
                'scoreCard.trackerSchedule.trackerDetails'
            ])
            ->where('id', $id)
            ->whereHas('scoreCard.masterSchedule', function ($q) use ($storeId) {

                // ✅ Only restrict if store manager
                if ($storeId !== null) {
                    $q->where('store_id', $storeId);
                }
            })
            ->firstOrFail();
    }
 
  

     
    
    public function deletePayroll(?int $storeId, int $payrollId): void
    {
        $payroll = Payroll::query()
            ->where('id', $payrollId)
            ->when($storeId, function ($q) use ($storeId) {
                $q->whereHas('scoreCard.masterSchedule', function ($q2) use ($storeId) {
                    $q2->where('store_id', $storeId);
                });
            })
            ->firstOrFail();

        if ($payroll->paymentStatus !== 'pending') {
            throw new \Exception('Only pending payroll can be deleted.');
        }

        $payroll->delete(); // soft delete
    }
    public function restorePayroll(?int $storeId, int $payrollId): void
    {
        $payroll = Payroll::onlyTrashed()
            ->where('id', $payrollId)
            ->when($storeId, function ($q) use ($storeId) {
                $q->whereHas('scoreCard.masterSchedule', function ($q2) use ($storeId) {
                    $q2->where('store_id', $storeId);
                });
            })
            ->firstOrFail();

        $payroll->restore();
    }
    public function forceDeletePayroll(?int $storeId, int $payrollId): void
    {
        $payroll = Payroll::onlyTrashed()
            ->where('id', $payrollId)
            ->when($storeId, function ($q) use ($storeId) {
                $q->whereHas('scoreCard.masterSchedule', function ($q2) use ($storeId) {
                    $q2->where('store_id', $storeId);
                });
            })
            ->firstOrFail();

        if ($payroll->paymentStatus !== 'pending') {
            throw new \Exception('Only pending payroll can be permanently deleted.');
        }

        $payroll->forceDelete();
    }
}