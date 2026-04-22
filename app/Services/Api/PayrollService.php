<?php 
namespace App\Services\Api;

use App\Models\Payroll;
use App\Models\ScoreCard;
use App\Models\Loan;
use App\Models\Deduction;
use App\Models\Apartment;
use App\Models\Sim;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function createPayroll(array $data)
    {
        DB::beginTransaction();
        try {
            // Get the scorecard for the employee
            $scoreCard = ScoreCard::findOrFail($data['scoreCardId']);
            $employee = $scoreCard->employee;

            // Check if the final salary is below zero
            if ($scoreCard->finalSalary <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Final salary cannot be below zero. Please review the data.'
                ], 400); // Bad Request
            }

            // Fetch the loan from employeesloans (instead of directly from loans)
            $employeeLoan = \App\Models\EmployeeLoan::where('employeeId', $employee->id)
                ->where('loanStatus', 'active') // Ensure loan status is 'active'
                ->first();
            
            // Initialize loan related deductions
            $loanAmountWithTax = 0;
            $loanDeduction = 0;

            // Check if the employee has an active loan
            if ($employeeLoan) {
                $loan = \App\Models\Loan::find($employeeLoan->loansId);
                if ($loan) {
                    $loanAmountWithTax = $loan->loanAmountWithTax;

                    // Adjust loan based on loan type (phone or car)
                    if ($loan->loanType === 'phone') {
                        $loanDeduction = 75;
                    } elseif ($loan->loanType === 'car') {
                        $loanDeduction = 150;
                    }

                    // Ensure loan deduction does not exceed loan balance
                    if ($loanAmountWithTax < $loanDeduction) {
                        $loanDeduction = $loanAmountWithTax;
                    }
                    if ($employeeLoan->loanRentAmount < $loanDeduction) {
                        $loanDeduction = $employeeLoan->loanRentAmount;
                    }
                }
            }

            // Initialize apartment and sim values
            $apartmentDeduction = 0;
            $simDeduction = 0;

            // If totalHoursWorked <= 30, do not subtract apartment and sim
            if ($scoreCard->totalHoursWorked > 30) {
                // Deduct apartment rent and sim card installment if available
                $deduction = \App\Models\Deduction::where('employeeId', $employee->id)->first();
                if ($deduction) {
                    $apartmentDeduction = \App\Models\Apartment::find($deduction->ApartmentId)->ApartmentRent;
                    $simDeduction = \App\Models\Sim::find($deduction->SimId)->simCardInstallment;
                } else {
                    $apartmentDeduction = 0;
                    $simDeduction = 0;
                }
            }

            // Calculate the sum of deductions and loans
            $totalDeductions = $apartmentDeduction + $simDeduction;

            // If final salary is less than loanDeduction, subtract only the remaining balance
            if ($scoreCard->finalSalary <= $loanDeduction) {
                $remainingLoanDeduction = $loanDeduction - $scoreCard->finalSalary;
                $loanDeduction = $scoreCard->finalSalary;  // Subtract the remaining amount from loan
                if ($employeeLoan) {
                    $employeeLoan->loanRentAmount -= $loanDeduction;
                    if ($employeeLoan->loanRentAmount == 0) {
                        $employeeLoan->loanStatus = "completed";
                    }
                    $employeeLoan->save();
                }

                // Update final salary after loan deduction
                $finalSalary = 0;  // Final salary is now 0 after deduction
                // Create the payroll record
                $payroll = \App\Models\Payroll::create([
                    'scorecardId' => $scoreCard->id,
                    'loanAmount' => $loanAmountWithTax,
                    'deductions' => $apartmentDeduction + $simDeduction,
                    'finalSalary' => $finalSalary,
                    'paymentStatus' => 'pending', // Default status
                ]);

                DB::commit();
                 // Return the payroll details with deductions breakdown
            return response()->json([
                'success' => true,
                'payroll' => $payroll,
                'loanDeduction' => $loanDeduction,
                'apartmentDeduction' => $apartmentDeduction,
                'simDeduction' => $simDeduction,
                'finalSalary' => $finalSalary,
                'message' => 'Payroll created successfully with deductions breakdown.'
            ]);
            } else {
                $finalSalary = $scoreCard->finalSalary - $totalDeductions;

                if ($finalSalary <= $loanDeduction) {
                    $loanDeduction = $finalSalary;
                    if ($employeeLoan) {
                        $employeeLoan->loanRentAmount -= $loanDeduction;
                        if ($employeeLoan->loanRentAmount == 0) {
                            $employeeLoan->loanStatus = "completed";
                        }
                        $employeeLoan->save();
                    }

                    // Update final salary after loan deduction
                    $finalSalary = 0;
                    // Create the payroll record
                    $payroll = \App\Models\Payroll::create([
                        'scorecardId' => $scoreCard->id,
                        'loanAmount' => $loanAmountWithTax,
                        'deductions' => $apartmentDeduction + $simDeduction,
                        'finalSalary' => $finalSalary,
                        'paymentStatus' => 'pending', // Default status
                    ]);
                    DB::commit();
                     // Return the payroll details with deductions breakdown
                return response()->json([
                    'success' => true,
                    'payroll' => $payroll,
                    'loanDeduction' => $loanDeduction,
                    'apartmentDeduction' => $apartmentDeduction,
                    'simDeduction' => $simDeduction,
                    'finalSalary' => $finalSalary,
                    'message' => 'Payroll created successfully with deductions breakdown.'
                ]);
                }

                // Further loan deduction if any
                $finalSalary -= $loanDeduction;

                // Save remaining loan balance
                if ($employeeLoan) {
                    $employeeLoan->loanRentAmount -= $loanDeduction;
                    if ($employeeLoan->loanRentAmount == 0) {
                        $employeeLoan->loanStatus = "completed";
                    }
                    $employeeLoan->save();
                }

                // Create the payroll record
                $payroll = \App\Models\Payroll::create([
                    'scorecardId' => $scoreCard->id,
                    'loanAmount' => $loanAmountWithTax,
                    'deductions' => $apartmentDeduction + $simDeduction,
                    'finalSalary' => $finalSalary,
                    'paymentStatus' => 'pending', // Default status
                ]);

                DB::commit();
            }

            // Return the payroll details with deductions breakdown
            return response()->json([
                'success' => true,
                'payroll' => $payroll,
                'loanDeduction' => $loanDeduction,
                'apartmentDeduction' => $apartmentDeduction,
                'simDeduction' => $simDeduction,
                'finalSalary' => $finalSalary,
                'message' => 'Payroll created successfully with deductions breakdown.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Payroll creation failed: ' . $e->getMessage());
        }
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
    public function approveByThirdShiftStoreManager(int $payrollId)
    {
        $payroll = Payroll::findOrFail($payrollId);

        if ($payroll->paymentStatus !== 'pending') {
            throw new \Exception('Cannot approve, the payment status is not pending.');
        }

        $payroll->approvedByThirdShiftStoreManager = true;
        $payroll->approvedByThirdShiftStoreManagerId = auth()->id();
        $payroll->save();
    }

    // Approve payroll by Senior Manager
    public function approveBySeniorManager(int $payrollId)
    {
        $payroll = Payroll::findOrFail($payrollId);

        if ($payroll->paymentStatus !== 'pending') {
            throw new \Exception('Cannot approve, the payment status is not pending.');
        }

        $payroll->approvedBySeniorManager = true;
        $payroll->approvedBySeniorManagerId = auth()->id();
        $payroll->paymentStatus = 'paid'; // Change status to 'paid'
        $payroll->save();
    }
}